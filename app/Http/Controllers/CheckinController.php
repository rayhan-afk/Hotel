<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\CheckinRepositoryInterface;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\Amenity; 
use Carbon\Carbon;
use App\Helpers\Helper; 
use Illuminate\Support\Facades\DB; 

class CheckinController extends Controller
{
    private $checkinRepository;

    public function __construct(CheckinRepositoryInterface $checkinRepository)
    {
        $this->checkinRepository = $checkinRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->checkinRepository->getCheckinDatatable($request));
        }
        return view('transaction.checkin.index');
    }

    public function edit(Transaction $transaction)
    {
        $rooms = Room::all(); 
        return view('transaction.checkin.edit', compact('transaction', 'rooms'));
    }

    // === [METHOD UTAMA: TRIGGER CHECK-IN & POTONG STOK] ===
    public function processCheckIn($id)
    {
        DB::beginTransaction(); 
        try {
            $transaction = Transaction::findOrFail($id);

            // 1. Validasi Status
            if ($transaction->status !== 'Reservation') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Tamu sudah Check-In atau status tidak valid!'
                ], 400);
            }

            // 2. Validasi Tanggal (Satpam)
            $reservationDate = Carbon::parse($transaction->check_in)->startOfDay();
            $today = Carbon::now()->startOfDay(); 

            if ($reservationDate->gt($today)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal! Belum waktunya Check In. Jadwal tamu ini tanggal: ' . $reservationDate->format('d/m/Y')
                ], 400); 
            }

            // 3. Update Status
            $transaction->update([
                'status' => 'Check In',
                'check_in' => Carbon::now(), 
            ]);

            // 4. Logika Pengurangan Stok Amenities
            $room = $transaction->room;
            foreach ($room->amenities as $amenity) {
                if ($amenity->satuan != 'liter') {
                    $qtyNeeded = $amenity->pivot->amount; 
                    $amenity->decrement('stok', $qtyNeeded);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Check-In Berhasil! Waktu Masuk & Stok Amenities tercatat otomatis.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // === [METHOD UPDATE: EXTEND/EDIT TRANSAKSI (BERSIH DARI EXTRA BED/BK)] ===
    public function update(Request $request, $id)
    {
        $request->validate([
            'check_in'      => 'required|date', 
            'check_out'     => 'required|date|after:check_in',
            'breakfast'     => 'required|in:Yes,No',
            // 'extra_bed' & 'extra_breakfast' dihapus
        ]);

        DB::beginTransaction();
        
        try {
            $transaction = Transaction::with(['room', 'charges'])->findOrFail($id);

            // =========================================================
            // A. CARI TAHU HARGA PER MALAM "ASLI" (LOCK RATE)
            // =========================================================
            
            // 1. Hitung durasi LAMA
            $oldIn  = \Carbon\Carbon::parse($transaction->check_in)->startOfDay();
            $oldOut = \Carbon\Carbon::parse($transaction->check_out)->startOfDay();
            $oldDays = $oldIn->diffInDays($oldOut) ?: 1;

            // 2. Hitung komponen biaya LAMA (Hanya Charges & Breakfast Utama)
            $oldCharges   = $transaction->charges->sum('total');
            // Extra bed & bk lama diabaikan/dihapus dari logika hitung
            $oldBreakfastMain = ($transaction->breakfast == 'Yes') ? (100000 * $oldDays) : 0; 

            // 3. Dapatkan Harga Kamar Murni LAMA
            $oldGrandTotal = $transaction->total_price;
            
            // LOGIKA NETT: GrandTotal - Charges - Sarapan = Harga Kamar Murni
            $oldRoomPure = $oldGrandTotal - $oldCharges - $oldBreakfastMain;

            // 4. DAPATKAN RATE PER MALAM YANG DISEPAKATI
            $lockedPricePerNight = round($oldRoomPure / $oldDays); 

            // =========================================================
            // B. PROSES UPDATE SEPERTI BIASA (PAKAI LOCKED PRICE)
            // =========================================================
            
            // 1. Siapkan Waktu Baru
            $jamMasukAsli  = \Carbon\Carbon::parse($transaction->check_in)->format('H:i:s');
            $jamKeluarAsli = \Carbon\Carbon::parse($transaction->check_out)->format('H:i:s');
            $dbCheckInString  = $request->check_in . ' ' . $jamMasukAsli;
            $dbCheckOutString = $request->check_out . ' ' . $jamKeluarAsli;

            // 2. Cek Bentrok
            $newStart = \Carbon\Carbon::parse($dbCheckInString);
            $newEnd   = \Carbon\Carbon::parse($dbCheckOutString);

            $existingReservations = Transaction::where('room_id', $transaction->room_id)
                ->where('id', '!=', $transaction->id)
                ->whereIn('status', ['Reservation', 'Check In'])
                ->lockForUpdate()
                ->get();

            foreach ($existingReservations as $res) {
                $existingStart = \Carbon\Carbon::parse($res->check_in);
                $existingEnd   = \Carbon\Carbon::parse($res->check_out);
                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    DB::rollback();
                    $tamu = $res->customer ? $res->customer->name : 'Tamu Lain';
                    return response()->json(['status' => 'error', 'message' => "GAGAL! Bentrok dengan {$tamu}."], 422);
                }
            }

            // 3. Hitung Durasi BARU
            $newDateIn  = \Carbon\Carbon::parse($request->check_in)->startOfDay();
            $newDateOut = \Carbon\Carbon::parse($request->check_out)->startOfDay();
            $newDays = $newDateIn->diffInDays($newDateOut) ?: 1;

            // 4. Hitung Ulang Total (Menggunakan $lockedPricePerNight)
            $roomPriceTotal = $lockedPricePerNight * $newDays;
            $mainBreakfastPrice = ($request->breakfast == 'Yes') ? (100000 * $newDays) : 0;
            
            // HARGA TOTAL KAMAR & SARAPAN UTAMA
            $taxableAmount = $roomPriceTotal + $mainBreakfastPrice;

            // 5. Total Baru (Plus Jajanan Lama/Charges)
            $newGrandTotal = $taxableAmount + $oldCharges; 

            // 6. Simpan
            $transaction->update([
                'check_in'        => $dbCheckInString,  
                'check_out'       => $dbCheckOutString, 
                'breakfast'       => $request->breakfast,
                'total_price'     => $newGrandTotal
            ]);

            DB::commit();
            
            // Info Kurang/Lebih Bayar
            $alreadyPaid = (float) $transaction->paid_amount;
            $shortfall = $newGrandTotal - $alreadyPaid;

            if ($shortfall > 100) {
                $msg = 'Update Berhasil. Kurang Bayar: ' . Helper::convertToRupiah($shortfall);
                $status = 'warning';
            } elseif ($shortfall < -100) {
                $msg = 'Update Berhasil. Lebih Bayar: ' . Helper::convertToRupiah(abs($shortfall));
                $status = 'info';
            } else {
                $msg = 'Data berhasil diperbarui!';
                $status = 'success';
            }

            return response()->json(['status' => $status, 'message' => $msg]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
    
    // Method untuk Menghapus / Cancel Transaksi
    public function destroy($id)
    {
        $transaction = Transaction::with('room.amenities')->findOrFail($id);

        if ($transaction->status == 'Check In') {
            $room = $transaction->room;
            foreach ($room->amenities as $amenity) {
                if ($amenity->satuan != 'liter') {
                    $qtyBalik = $amenity->pivot->amount;
                    $amenity->increment('stok', $qtyBalik);
                }
            }
        }

        $this->checkinRepository->delete($id); 
        return response()->json(['message' => 'Data berhasil dihapus & Stok Amenities telah dikembalikan.']);
    }

    public function checkout($id)
    {
        $this->checkinRepository->checkoutGuest($id);
        return response()->json([
            'message' => 'Tamu berhasil Check-Out!',
            'redirect_url' => route('laporan.kamar.index')
        ]);
    }

    // === [METHOD BARU: BAYAR LUNAS (QUICK ACTION)] ===
    public function payRemaining(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);
            
            // Hitung Sisa
            $shortfall = $transaction->total_price - $transaction->paid_amount;

            if ($shortfall <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Transaksi ini sudah lunas!'], 400);
            }

            // 1. Buat Record Pembayaran
            \App\Models\Payment::create([
                'user_id'        => auth()->id(), // [FIX] Tambahkan User ID
                'transaction_id' => $id,
                'price'          => $shortfall,
                'status'         => 'Full Payment', // Menandakan pelunasan
                'type'           => 'Cash' // Default Cash
            ]);

            // 2. Update Paid Amount di Transaksi
            $transaction->increment('paid_amount', $shortfall);

            DB::commit();
            
            return response()->json([
                'status' => 'success', 
                'message' => 'Pembayaran lunas berhasil! Sisa tagihan Rp 0.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
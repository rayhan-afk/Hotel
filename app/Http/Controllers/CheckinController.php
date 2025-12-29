<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\CheckinRepositoryInterface;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\Amenity; // [BARU] Jangan lupa import ini
use Carbon\Carbon;
use App\Helpers\Helper; 
use Illuminate\Support\Facades\DB; // [BARU] Untuk Transaction DB

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
   // === [METHOD UTAMA: TRIGGER CHECK-IN & POTONG STOK] ===
    public function processCheckIn($id)
    {
        DB::beginTransaction(); // Pakai DB Transaction biar aman
        try {
            $transaction = Transaction::findOrFail($id);

            // 1. Validasi Status: Hanya boleh Check In jika statusnya Reservation
            if ($transaction->status !== 'Reservation') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Tamu sudah Check-In atau status tidak valid!'
                ], 400);
            }

            // === [VALIDASI TANGGAL (SATPAM) DITAMBAHKAN DISINI] ===
            // Ambil tanggal rencana checkin (jam diabaikan, fokus tanggal saja)
            $reservationDate = Carbon::parse($transaction->check_in)->startOfDay();
            $today = Carbon::now()->startOfDay(); // Tanggal hari ini (00:00)

            // Cek: Apakah Tanggal Reservasi LEBIH BESAR (Masa Depan) dari Hari Ini?
            // Contoh: Rencana tgl 25, Hari ini tgl 23 -> DITOLAK
            if ($reservationDate->gt($today)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal! Belum waktunya Check In. Jadwal tamu ini tanggal: ' . $reservationDate->format('d/m/Y')
                ], 400); // Pesan ini akan muncul sebagai Popup Error di layar
            }
            // ========================================================

            // 2. UPDATE STATUS & JAM CHECK IN REAL-TIME
            $transaction->update([
                'status' => 'Check In',
                
                // Update jam check_in menjadi DETIK INI JUGA.
                // (Hanya dieksekusi jika lolos dari satpam tanggal di atas)
                'check_in' => Carbon::now(), 
            ]);

            // 3. LOGIKA PENGURANGAN STOK AMENITIES (Tetap Sama/Tidak Dihapus)
            $room = $transaction->room;
            
            // Loop semua amenities yang terhubung dengan kamar ini
            foreach ($room->amenities as $amenity) {
                // Pastikan tipe barang bukan literan
                if ($amenity->satuan != 'liter') {
                    
                    // Ambil jatah per kamar dari tabel pivot
                    $qtyNeeded = $amenity->pivot->amount; 

                    // Kurangi stok di tabel amenities
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

   public function update(Request $request, $id)
    {
        $request->validate([
            'check_in'  => 'required|date', 
            'check_out' => 'required|date|after:check_in',
            'breakfast' => 'required|in:Yes,No',
            'extra_bed' => 'nullable|integer|min:0',
            'extra_breakfast' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        
        try {
            $transaction = Transaction::findOrFail($id);

            // =========================================================
            // 1. SIAPKAN WAKTU (JANGAN DI-HARDCODE)
            // =========================================================
            // Kita ambil jam asli yang tersimpan di database.
            // Contoh: Kalau tamu checkin jam 20:15, ya biarkan 20:15.
            
            $jamMasukAsli  = \Carbon\Carbon::parse($transaction->check_in)->format('H:i:s');
            $jamKeluarAsli = \Carbon\Carbon::parse($transaction->check_out)->format('H:i:s');

            // Gabungkan Tanggal Baru (dari Form) + Jam Asli (dari DB)
            $dbCheckInString  = $request->check_in . ' ' . $jamMasukAsli;
            $dbCheckOutString = $request->check_out . ' ' . $jamKeluarAsli;

            // =======================================================
            // 2. CEK BENTROK
            // =======================================================
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

            // =======================================================
            // 3. HITUNG HARGA (LOGIKA HARI MURNI)
            // =======================================================
            // Hitung durasi berdasarkan TANGGAL SAJA (Start of Day).
            // Ini biar harganya gak jadi Rp 0 kalau jamnya deketan.
            
            $dateInNoTime  = \Carbon\Carbon::parse($request->check_in)->startOfDay();
            $dateOutNoTime = \Carbon\Carbon::parse($request->check_out)->startOfDay();
            
            $days = $dateInNoTime->diffInDays($dateOutNoTime) ?: 1;

            $pricePerNight = (float) $transaction->room->price; 
            
            // Hitung Duit
            $roomPriceTotal = $pricePerNight * $days;
            $mainBreakfastPrice = ($request->breakfast == 'Yes') ? (100000 * $days) : 0;
            $taxableAmount = $roomPriceTotal + $mainBreakfastPrice;
            $tax = $taxableAmount * 0.10; 

            // Extra Items
            $qtyExtraBed = (int) $request->input('extra_bed', 0);
            $qtyExtraBreakfast = (int) $request->input('extra_breakfast', 0);
            $totalExtraBed = $qtyExtraBed * 200000;
            $totalExtraBreakfast = $qtyExtraBreakfast * 125000;

            $newGrandTotal = $taxableAmount + $tax + $totalExtraBed + $totalExtraBreakfast;
            $alreadyPaid = (float) $transaction->paid_amount;
            $shortfall = $newGrandTotal - $alreadyPaid;

            // =========================================================
            // 4. UPDATE DATABASE
            // =========================================================
            $transaction->update([
                'check_in'        => $dbCheckInString,  // Tgl Baru + Jam Lama
                'check_out'       => $dbCheckOutString, // Tgl Baru + Jam Lama
                'breakfast'       => $request->breakfast,
                'extra_bed'       => $qtyExtraBed, 
                'extra_breakfast' => $qtyExtraBreakfast,
                'total_price'     => $newGrandTotal
            ]);

            DB::commit();

            if ($shortfall > 100) {
                $msg = 'Simpan Berhasil. SISA BAYAR: ' . Helper::convertToRupiah($shortfall);
                $status = 'warning';
            } elseif ($shortfall < -100) {
                $msg = 'Simpan Berhasil. LEBIH BAYAR: ' . Helper::convertToRupiah(abs($shortfall));
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
    
    // Method destroy & checkout (Biarkan Saja)
    // Method untuk Menghapus / Cancel Transaksi
    public function destroy($id)
    {
        // 1. Ambil data transaksi dulu sebelum dihapus
        $transaction = Transaction::with('room.amenities')->findOrFail($id);

        // 2. CEK LOGIKA PENGEMBALIAN STOK (RESTORE STOCK)
        // Kita hanya balikin stok JIKA statusnya sudah 'Check In'.
        // (Kalau masih 'Reservation', stok belum dipotong, jadi gak perlu dibalikin).
        if ($transaction->status == 'Check In') {
            
            $room = $transaction->room;
            
            // Loop amenities kamar tersebut
            foreach ($room->amenities as $amenity) {
                if ($amenity->satuan != 'liter') {
                    $qtyBalik = $amenity->pivot->amount;
                    
                    // KEMBALIKAN STOK (Increment)
                    $amenity->increment('stok', $qtyBalik);
                }
            }
        }

        // 3. Baru setelah stok aman, data dihapus dari database
        // (Bisa pakai repository atau langsung model)
        $this->checkinRepository->delete($id); 
        
        return response()->json(['message' => 'Data berhasil dihapus & Stok Amenities telah dikembalikan.']);
    }

    public function checkout($id)
    {
        $this->checkinRepository->checkoutGuest($id);
        return response()->json([
            'message' => 'Tamu berhasil Check-Out!',
            'redirect_url' => route('laporan.kamar.index') // Ganti sesuai route kamu
        ]);
    }
}
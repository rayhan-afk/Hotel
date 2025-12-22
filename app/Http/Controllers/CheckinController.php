<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\CheckinRepositoryInterface;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Helpers\Helper; 

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

    // === [METHOD UTAMA: AMAN DARI BENTROK & AMAN DARI INFLASI HARGA] ===
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'room_id'   => 'required|exists:rooms,id', 
            'check_in'  => 'required|date', 
            'check_out' => 'required|date|after:check_in',
            'breakfast' => 'required|in:Yes,No',
            'extra_bed' => 'nullable|integer|min:0',
            'extra_breakfast' => 'nullable|integer|min:0',
        ]);

        $transaction = Transaction::findOrFail($id);
        
        $newCheckIn = Carbon::parse($request->check_in);
        $newCheckOut = Carbon::parse($request->check_out);

        // ---------------------------------------------------------
        // FITUR 1: CEK BENTROK JADWAL (COLLISION CHECK)
        // ---------------------------------------------------------
        $collision = Transaction::where('room_id', $transaction->room_id)
            ->where('id', '!=', $transaction->id)
            ->whereIn('status', ['Reservation', 'Check In'])
            ->where(function ($q) use ($newCheckIn, $newCheckOut) {
                $q->where('check_in', '<', $newCheckOut)
                  ->where('check_out', '>', $newCheckIn);
            })
            ->with('customer')
            ->first();

        if ($collision) {
            $nabrakSiapa = $collision->customer ? $collision->customer->name : 'Tamu Lain';
            $tglNabrak = Carbon::parse($collision->check_in)->format('d/m/Y');
            
            return response()->json([
                'status' => 'error',
                'message' => "Gagal Extend! Kamar ini sudah di-booking oleh {$nabrakSiapa} mulai tanggal {$tglNabrak}."
            ], 422);
        }

        // ---------------------------------------------------------
        // FITUR 2: KUNCI HARGA (PRICE LOCK)
        // ---------------------------------------------------------
        $oldIn = Carbon::parse($transaction->check_in);
        $oldOut = Carbon::parse($transaction->check_out);
        $oldDays = $oldIn->diffInDays($oldOut) ?: 1;

        // Reverse Engineering (Mundur dari Grand Total Lama)
        $oldSubTotal = $transaction->total_price / 1.10; 
        
        $oldBreakfastCost = ($transaction->breakfast == 'Yes') ? (100000 * $oldDays) : 0;
        $oldExtraBedCost = ((int)$transaction->extra_bed) * 200000 * $oldDays;
        $oldExtraBreakfastCost = ((int)$transaction->extra_breakfast) * 125000 * $oldDays;
        
        $oldRoomTotalPure = $oldSubTotal - $oldBreakfastCost - $oldExtraBedCost - $oldExtraBreakfastCost;
        $pricePerNight = $oldRoomTotalPure / $oldDays;

        // ---------------------------------------------------------
        // 3. HITUNG ULANG (HARGA KUNCIAN + LAYANAN BARU)
        // ---------------------------------------------------------
        
        // A. Hitung Durasi Baru
        $dayDifference = $newCheckIn->diffInDays($newCheckOut);
        if ($dayDifference < 1) $dayDifference = 1;

        // B. Total Harga Kamar (Base)
        $roomPriceTotal = $pricePerNight * $dayDifference;
        
        // C. Hitung Sarapan Utama
        $breakfastPrice = 0;
        if($request->breakfast == 'Yes') {
            $breakfastPrice = 100000 * $dayDifference;
        }

        // D. Hitung Extra (Pastikan di-cast ke integer)
        // [BARU] C. Hitung Biaya Extra Baru
        $qtyExtraBed = (int) $request->input('extra_bed', 0);
        $qtyExtraBreakfast = (int) $request->input('extra_breakfast', 0);

        // --- PERBAIKAN DI SINI ---
        
        // 1. Extra Bed = FLAT (Hanya dikali Jumlah Bed, TIDAK dikali durasi hari)
        $extraBedTotal = $qtyExtraBed * 200000; 

        // 2. Extra Breakfast = PER HARI (Dikali Jumlah Porsi x Durasi Hari)
        // (Logikanya orang makan tiap pagi)
        $extraBreakfastTotal = ($qtyExtraBreakfast * 125000) * $dayDifference;

        // E. Hitung Grand Total
        $subTotal   = $roomPriceTotal + $breakfastPrice + $extraBedTotal + $extraBreakfastTotal;
        $tax        = $subTotal * 0.10;
        $grandTotal = $subTotal + $tax;

        // ---------------------------------------------------------
        // 4. UPDATE DATABASE (CRITICAL PART)
        // ---------------------------------------------------------
        $transaction->update([
            'check_in'        => $request->check_in,
            'check_out'       => $request->check_out,
            'breakfast'       => $request->breakfast,
            
            // Kolom ini WAJIB ada di $fillable Model Transaction
            'extra_bed'       => $qtyExtraBed, 
            'extra_breakfast' => $qtyExtraBreakfast,
            
            'total_price'     => $grandTotal
        ]);

        // ---------------------------------------------------------
        // FITUR 3: CEK KEUANGAN
        // ---------------------------------------------------------
        $alreadyPaid = $transaction->paid_amount;
        $shortfall = $grandTotal - $alreadyPaid;

        if ($shortfall > 0) {
            $msg = 'Update Berhasil. Tamu KURANG BAYAR ' . Helper::convertToRupiah($shortfall) . '. Mohon segera minta pelunasan!';
            $status = 'warning';
        } elseif ($shortfall < 0) {
            $refund = abs($shortfall);
            $msg = 'Update Berhasil. Tamu LEBIH BAYAR ' . Helper::convertToRupiah($refund) . '. Cek prosedur refund.';
            $status = 'info';
        } else {
            $msg = 'Data berhasil diperbarui!';
            $status = 'success';
        }

        return response()->json([
            'status' => $status,
            'message' => $msg
        ]);
    }
    
    // ... Method destroy & checkout biarkan saja ...
    public function destroy($id)
    {
        $this->checkinRepository->delete($id);
        return response()->json(['message' => 'Reservasi berhasil dihapus (Cancel).']);
    }

    public function checkout($id)
    {
        $this->checkinRepository->checkoutGuest($id);
        return response()->json([
            'message' => 'Tamu berhasil Check-Out!',
            'redirect_url' => route('laporan.kamar.index')
        ]);
    }
}
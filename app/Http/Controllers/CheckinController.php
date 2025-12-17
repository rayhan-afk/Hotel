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
        ]);

        $transaction = Transaction::findOrFail($id);
        
        $newCheckIn = Carbon::parse($request->check_in);
        $newCheckOut = Carbon::parse($request->check_out);

        // ---------------------------------------------------------
        // FITUR 1: CEK BENTROK JADWAL (COLLISION CHECK)
        // ---------------------------------------------------------
        // Pastikan perpanjangan tanggal tidak menabrak jadwal tamu lain
        $collision = Transaction::where('room_id', $transaction->room_id) // Pakai room_id asli (karena gabisa pindah)
            ->where('id', '!=', $transaction->id)
            ->whereIn('status', ['Reservation', 'Check In'])
            ->where(function ($q) use ($newCheckIn, $newCheckOut) {
                // Rumus Tabrakan: (StartA < EndB) && (EndA > StartB)
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
        // Kita BONGKAR harga lama untuk menemukan harga per malam yang asli.
        // Tujuannya: Agar harga tidak berubah meskipun Admin menaikkan harga di Master Data.
        
        // A. Hitung durasi lama
        $oldIn = Carbon::parse($transaction->check_in);
        $oldOut = Carbon::parse($transaction->check_out);
        $oldDays = $oldIn->diffInDays($oldOut) ?: 1;

        // B. Reverse Engineering (Mundur dari Grand Total)
        // Rumus: GrandTotal = (HargaKamar + BiayaSarapan) * 1.1 (Pajak)
        
        $oldSubTotal = $transaction->total_price / 1.10; // Hilangkan Pajak 10%
        
        // Hilangkan Biaya Sarapan Lama
        $oldBreakfastCost = ($transaction->breakfast == 'Yes') ? (140000 * $oldDays) : 0;
        
        // Ketemu Total Harga Kamar Murni
        $oldRoomTotalPure = $oldSubTotal - $oldBreakfastCost;
        
        // KETEMU! Ini harga deal per malamnya.
        $pricePerNight = $oldRoomTotalPure / $oldDays;


        // ---------------------------------------------------------
        // 3. HITUNG ULANG DENGAN HARGA KUNCIAN
        // ---------------------------------------------------------
        
        // Hitung Durasi Baru
        $dayDifference = $newCheckIn->diffInDays($newCheckOut);
        if ($dayDifference < 1) $dayDifference = 1;

        // Total Harga Kamar Baru (Pakai Harga Kuncian $pricePerNight)
        $roomPriceTotal = $pricePerNight * $dayDifference;
        
        // Hitung Biaya Sarapan Baru
        $breakfastPrice = 0;
        if($request->breakfast == 'Yes') {
            $breakfastPrice = 140000 * $dayDifference;
        }

        // Hitung Pajak & Grand Total
        $subTotal   = $roomPriceTotal + $breakfastPrice;
        $tax        = $subTotal * 0.10;
        $grandTotal = $subTotal + $tax;

        // 4. Update Database
        $transaction->update([
            // room_id TIDAK DIUPDATE karena fitur pindah kamar tidak ada
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'breakfast'   => $request->breakfast,
            'total_price' => $grandTotal
        ]);

        // ---------------------------------------------------------
        // FITUR 3: CEK KEUANGAN (KURANG BAYAR)
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
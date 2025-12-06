<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\CheckinRepositoryInterface;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;

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

    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'room_id'   => 'required|exists:rooms,id',
            'check_in'  => 'required|date', 
            'check_out' => 'required|date|after:check_in',
            'breakfast' => 'required|in:Yes,No', // Pastikan validasi ini ada
        ]);

        // 2. Ambil Transaksi & Kamar
        $transaction = Transaction::findOrFail($id);
        $room = Room::findOrFail($request->room_id);

        // 3. Hitung Durasi Baru
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        // Hitung selisih hari (minimal 1 hari)
        $dayDifference = $checkIn->diffInDays($checkOut);
        if ($dayDifference < 1) {
            $dayDifference = 1;
        }

        // 4. Hitung Ulang Total Harga
        $roomPriceTotal = $room->price * $dayDifference;
        
        // Hitung Biaya Sarapan (140rb/malam jika Yes)
        $breakfastPrice = 0;
        // Cek input breakfast dari request form
        if($request->breakfast == 'Yes') {
            $breakfastPrice = 140000 * $dayDifference;
        }

        // Hitung Pajak
        $subTotal   = $roomPriceTotal + $breakfastPrice;
        $tax        = $subTotal * 0.10; // Pajak 10%
        $grandTotal = $subTotal + $tax;

        // 5. Update Database
        $transaction->update([
            'room_id'     => $request->room_id,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'breakfast'   => $request->breakfast, // Simpan status sarapan baru
            'total_price' => $grandTotal          // Simpan harga baru
        ]);

        return response()->json(['message' => 'Data berhasil diperbarui! Total harga baru: ' . \App\Helpers\Helper::convertToRupiah($grandTotal)]);
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
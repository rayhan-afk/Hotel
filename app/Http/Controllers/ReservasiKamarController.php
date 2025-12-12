<?php

namespace App\Http\Controllers;

use App\Models\Transaction; // Jangan lupa use Model Transaction
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use Illuminate\Http\Request;

class ReservasiKamarController extends Controller
{
    private $reservasiRepository;

    public function __construct(ReservasiKamarRepositoryInterface $reservasiRepository)
    {
        $this->reservasiRepository = $reservasiRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json(
                $this->reservasiRepository->getDatatable($request)
            );
        }
        return view('room-info.reservation');
    }

    // === METHOD BARU UNTUK BATALKAN ===
    public function cancel($id)
    {
        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($id);
        
        // Ubah status jadi Cancel
        $transaction->update([
            'status' => 'Cancel'
        ]);

        // Kembalikan response sukses ke JS
        return response()->json(['message' => 'Reservasi berhasil dibatalkan']);
    }

    public function checkIn($id)
    {
        $transaction = \App\Models\Transaction::findOrFail($id);

        if($transaction->status == 'Reservation') {
            
            // [PERBAIKAN] Cek apakah paid_amount masih 0? Jika ya, anggap sudah lunas saat Check In
            $paidAmount = $transaction->paid_amount;
            if ($paidAmount == 0) {
                $paidAmount = $transaction->total_price;
            }

            $transaction->update([
                'status' => 'Check In',
                'paid_amount' => $paidAmount // Update paid_amount
            ]);
            
            return response()->json(['message' => 'Berhasil Check In! Tamu kini berstatus aktif.']);
        }

        return response()->json(['message' => 'Gagal, status transaksi tidak valid.'], 400);
    }
}
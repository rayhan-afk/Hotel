<?php

namespace App\Http\Controllers;

use App\Models\Transaction; 
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon; // <--- JANGAN LUPA IMPORT CARBON

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

    public function cancel($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $transaction->update([
            'status' => 'Cancel'
        ]);

        return response()->json(['message' => 'Reservasi berhasil dibatalkan']);
    }

    // === [METHOD UTAMA YANG DIPERBAIKI] ===
    public function checkIn($id)
    {
        $transaction = Transaction::findOrFail($id);

        // 1. VALIDASI TANGGAL (SATPAM)
        // Ambil tanggal check in dan tanggal hari ini (jam diabaikan, fokus ke tanggal saja)
        $checkInDate = Carbon::parse($transaction->check_in)->startOfDay();
        $today = Carbon::today();

        // Jika Check In > Hari Ini (Artinya belum waktunya)
        if ($checkInDate->gt($today)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal! Belum waktunya Check In. Tanggal reservasi adalah: ' . $checkInDate->format('d/m/Y')
            ], 422); // Kode 422 artinya "Data tidak valid" (Ditolak)
        }

        // 2. PROSES CHECK IN
        if($transaction->status == 'Reservation') {
            
            // Cek pembayaran
            $paidAmount = $transaction->paid_amount;
            if ($paidAmount == 0) {
                $paidAmount = $transaction->total_price;
            }

            $transaction->update([
                'status' => 'Check In',
                'paid_amount' => $paidAmount
            ]);
            
            return response()->json(['message' => 'Berhasil Check In! Tamu kini berstatus aktif.']);
        }

        return response()->json(['message' => 'Gagal, status transaksi tidak valid.'], 400);
    }
}
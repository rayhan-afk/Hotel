<?php

namespace App\Http\Controllers;

use App\Models\Transaction; 
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class ReservasiKamarController extends Controller
{
    private $reservasiRepository;

    public function __construct(ReservasiKamarRepositoryInterface $reservasiRepository)
    {
        $this->reservasiRepository = $reservasiRepository;
    }

    public function index(Request $request)
    {
        // === [FITUR ANTI ZOMBIE] ===
        // Cari semua Reservasi yang tanggal check-in nya SUDAH LEWAT (kurang dari hari ini)
        // Contoh: Booking tgl 24, sekarang tgl 25. Berarti dia "No Show".
        Transaction::where('status', 'Reservation')
            ->whereDate('check_in', '<', Carbon::today()) 
            ->update(['status' => 'Cancel']); 
        // Status bisa diubah jadi 'Cancel' atau 'No Show' (sesuai selera)
        // ===========================

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

    // === [METHOD UTAMA YANG DIPERBAIKI: AUTO JAM MASUK] ===
    public function checkIn($id)
    {
        
        $transaction = Transaction::findOrFail($id);

        // 1. VALIDASI TANGGAL (SATPAM)
        $checkInDate = Carbon::parse($transaction->check_in)->startOfDay();
        $today = Carbon::today();

        // Jika Check In > Hari Ini (Artinya belum waktunya)
        if ($checkInDate->gt($today)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Gagal! Belum waktunya Check In. Tanggal reservasi adalah: ' . $checkInDate->format('d/m/Y')
            ], 422); 
        }

        // 2. PROSES CHECK IN
        if($transaction->status == 'Reservation') {
            
            $transaction->update([
                'status' => 'Check In',
                
                // [PENTING] Update jam check_in menjadi SEKARANG (Real-time)
                // Agar tercatat tamu masuk jam berapa.
                'check_in' => Carbon::now(),
                
                // Pastikan Lunas (Sisa Bayar 0)
                'paid_amount' => $transaction->total_price 
            ]);
            
            return response()->json(['message' => 'Berhasil Check In! Waktu tercatat & Pembayaran Lunas.']);
        }

        return response()->json(['message' => 'Gagal, status transaksi tidak valid.'], 400);
    }
}
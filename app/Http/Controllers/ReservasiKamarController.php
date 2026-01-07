<?php

namespace App\Http\Controllers;

use App\Models\Transaction; 
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use Illuminate\Http\Request; // Pastikan Request di-import
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
        // Cari reservasi lewat tanggal Check-in, ubah jadi 'Canceled' (No Show)
        Transaction::where('status', 'Reservation')
            ->whereDate('check_in', '<', Carbon::today()) 
            ->update([
                'status' => 'Canceled', // Samakan statusnya jadi 'Canceled'
                'cancel_reason' => 'No Show', // Alasan otomatis
                'cancel_notes' => 'Dibatalkan sistem karena tamu tidak datang pada tanggal check-in.'
            ]); 
        // ===========================

        if ($request->ajax()) {
            return response()->json(
                $this->reservasiRepository->getDatatable($request)
            );
        }
        return view('room-info.reservation');
    }

    // === [METHOD CANCEL YANG DIPERBAIKI] ===
    public function cancel(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Update Status & Simpan Alasan
        $transaction->update([
            'status'        => 'Canceled', // Gunakan 'Canceled' agar badge merah
            'cancel_reason' => $request->cancel_reason, // Dari Dropdown Modal
            'cancel_notes'  => $request->cancel_notes   // Dari Textarea Modal
        ]);

        return response()->json(['message' => 'Reservasi berhasil dibatalkan']);
    }

    // === [METHOD CHECK IN] ===
    public function checkIn($id)
    {
        $transaction = Transaction::findOrFail($id);

        // 1. VALIDASI TANGGAL (SATPAM)
        $checkInDate = Carbon::parse($transaction->check_in)->startOfDay();
        $today = Carbon::today();

        // Jika Check In > Hari Ini (Belum waktunya)
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
                
                // Update jam check_in menjadi SEKARANG (Real-time)
                'check_in' => Carbon::now(),
                
                // Pastikan Lunas (Sisa Bayar 0)
                'paid_amount' => $transaction->total_price 
            ]);
            
            return response()->json(['message' => 'Berhasil Check In! Waktu tercatat & Pembayaran Lunas.']);
        }

        return response()->json(['message' => 'Gagal, status transaksi tidak valid.'], 400);
    }
}
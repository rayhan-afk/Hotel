<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // === STATS CARD ===
        
        // 1. Kamar Terpakai (Status: Check In)
        $occupiedRoomsCount = Transaction::where('status', 'Check In')->count();

        // 2. Kamar Dibersihkan (Status Fisik Kamar)
        $cleaningRoomsCount = Room::where('status', 'Cleaning')->count();

        // 3. Reservasi Datang / Belum Check In
        $todayReservationsCount = Transaction::where('status', 'Reservation')
            ->whereDate('check_in', '>=', Carbon::today()) 
            ->count();

        // 4. Kamar Tersedia (Total - Sibuk)
        $totalRooms = Room::count();
        $unavailableCount = $occupiedRoomsCount + $cleaningRoomsCount;
        
        $availableRoomsCount = $totalRooms - $unavailableCount;
        if ($availableRoomsCount < 0) $availableRoomsCount = 0;

        // === [PERBAIKAN UTAMA: TAMU BULANAN] ===
        // Masalah sebelumnya: Hanya menghitung 'Check In'/'Reservation', jadi pas Checkout datanya hilang.
        // Solusi: Hitung semua transaksi bulan ini yang statusnya BUKAN 'Cancel'.
        // Ini otomatis mencakup: Reservation, Check In, DAN Payment Success (History Tamu).
        
        $thisMonth = Transaction::whereMonth('check_in', Carbon::now()->month)
            ->whereYear('check_in', Carbon::now()->year)
            ->where('status', '!=', 'Cancel') // <-- KUNCI: Jangan filter status aktif saja, tapi filter "Tidak Batal"
            ->count();


        // === TABEL DASHBOARD (Tamu Hari Ini) ===
        // Menampilkan daftar tamu yang sedang aktif (Check In) atau akan datang hari ini.
        $transactions = Transaction::with('user', 'room', 'customer')
            ->whereDate('check_in', '<=', Carbon::today())
            ->whereDate('check_out', '>=', Carbon::today())
            ->where(function($q) {
                $q->where('status', 'Check In')
                  ->orWhere('status', 'Reservation');
            })
            ->orderBy('status', 'ASC') 
            ->orderBy('check_in', 'ASC')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'availableRoomsCount',
            'occupiedRoomsCount',
            'cleaningRoomsCount',
            'todayReservationsCount',
            'thisMonth', // <-- Jangan lupa kirim variabel ini ke View
            'transactions'
        ));
    }
}
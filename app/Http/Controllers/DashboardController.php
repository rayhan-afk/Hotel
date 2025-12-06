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

        // 3. Reservasi Datang / Belum Check In (PERBAIKAN UTAMA)
        // Hitung semua tamu yang statusnya masih 'Reservation'.
        // Tidak peduli jam berapa, pokoknya kalau masih 'Reservation' dan jadwalnya
        // adalah HARI INI atau MASA DEPAN, berarti dia masuk hitungan.
        // Kita gunakan whereDate (>= hari ini) agar reservasi jam berapapun hari ini tetap masuk.
        
        $todayReservationsCount = Transaction::where('status', 'Reservation')
            ->whereDate('check_in', '>=', Carbon::today()) // Perbaikan: Pakai whereDate
            ->count();

        // 4. Kamar Tersedia (Total - Sibuk)
        $totalRooms = Room::count();
        $unavailableCount = $occupiedRoomsCount + $cleaningRoomsCount;
        
        $availableRoomsCount = $totalRooms - $unavailableCount;
        if ($availableRoomsCount < 0) $availableRoomsCount = 0;

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
            'transactions'
        ));
    }
}
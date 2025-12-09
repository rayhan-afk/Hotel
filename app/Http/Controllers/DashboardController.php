<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // === [LOGIC BARU] CEGAH DAPUR MASUK DASHBOARD ===
        // Jika yang login adalah orang Dapur, paksa pindah ke halaman Bahan Baku.
        if (Auth::user()->role === 'Dapur') {
            return redirect()->route('ingredient.index');
        }

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
        // Hitung semua transaksi bulan ini yang statusnya BUKAN 'Cancel'.
        $thisMonth = Transaction::whereMonth('check_in', Carbon::now()->month)
            ->whereYear('check_in', Carbon::now()->year)
            ->where('status', '!=', 'Cancel') 
            ->count();

        // === TABEL DASHBOARD (Tamu Hari Ini) ===
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
            'thisMonth',
            'transactions'
        ));
    }
}
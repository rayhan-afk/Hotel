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
        // 1. CEK ROLE DAPUR
        if (Auth::user()->role === 'Dapur') {
            return redirect()->route('ingredient.index');
        }

        // === [STATS CARD] ===
        
        // A. Kamar Terpakai (Status: Check In)
        $occupiedRoomsCount = Transaction::where('status', 'Check In')->count();

        // B. Kamar Dibersihkan (Status Fisik Kamar)
        $cleaningRoomsCount = Room::where('status', 'Cleaning')->count();

        // C. Reservasi Aktif Hari Ini (YANG BELUM CHECK IN)
        // (Digunakan untuk perhitungan ketersediaan kamar, biar akurat)
        $reservedNowCount = Transaction::where('status', 'Reservation')
            ->whereDate('check_in', '<=', Carbon::today()) 
            ->whereDate('check_out', '>', Carbon::today())
            ->count();

        // D. Reservasi Datang Hari Ini (Untuk Tampilan Card Dashboard)
        // Kita pakai nama variabel lama ($todayReservationsCount) agar View tidak error.
        // TAPI logic-nya sudah kita perbaiki: Hanya hitung yang Check-In HARI INI.
        $todayReservationsCount = Transaction::where('status', 'Reservation')
                ->whereDate('check_in', '>=', Carbon::today()) 
                ->count();

        // E. HITUNG KAMAR TERSEDIA (Total - Sibuk)
        $totalRooms = Room::count();
        
        // Sibuk = Sedang Inap + Sedang Dibersihkan + Sudah Dipesan Hari Ini
        $unavailableCount = $occupiedRoomsCount + $cleaningRoomsCount + $reservedNowCount;
        
        $availableRoomsCount = $totalRooms - $unavailableCount;
        if ($availableRoomsCount < 0) $availableRoomsCount = 0;

        // F. Tamu Bulan Ini (Statistik)
        $thisMonth = Transaction::whereMonth('check_in', Carbon::now()->month)
            ->whereYear('check_in', Carbon::now()->year)
            ->where('status', '!=', 'Cancel') 
            ->count();

        // === [TABEL DASHBOARD] ===
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

        // Return View (Syntax compact sekarang sudah benar)
        return view('dashboard.index', compact(
            'availableRoomsCount',
            'occupiedRoomsCount',
            'cleaningRoomsCount',
            'todayReservationsCount', // Nama variabel sudah sesuai, tidak perlu panah '=>'
            'thisMonth',
            'transactions'
        ));
    }
}
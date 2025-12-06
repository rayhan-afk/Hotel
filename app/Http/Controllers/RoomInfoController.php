<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomInfoController extends Controller
{
    public function index()
    {
        // Ambil semua kamar beserta relasi transaksi aktifnya
        $rooms = Room::with(['transactions' => function($q) {
            // Kita eager load transaksi yang relevan saja agar ringan
            $q->where('status', 'Check In')
              ->orWhere('status', 'Cleaning')
              ->orWhere('status', 'Reservation');
        }])->get();

        return view('room.index', compact('rooms'));
    }

    // === BAGIAN 1: KAMAR TERSEDIA ===
    public function availableRooms()
    {
        // Filter kamar yang status dinamisnya 'Available'
        $rooms = Room::all()->filter(function ($room) {
            return $room->dynamic_status === 'Available';
        });

        return view('room-info.available', [
            'rooms' => $rooms,
            'title' => 'Kamar Tersedia'
        ]);
    }

    // === BAGIAN 2: RESERVASI (AKAN DATANG) ===
    public function pendingReservations()
    {
        // Ambil transaksi Reservasi yang belum Check-in
        $transactions = Transaction::with('user', 'room', 'customer')
            ->where('status', 'Reservation')
            ->whereDate('check_in', '>=', Carbon::today())
            ->orderBy('check_in', 'ASC')
            ->get();

        return view('room-info.reservation', [
            'transactions' => $transactions,
            'title' => 'Reservasi Mendatang'
        ]);
    }

    // === BAGIAN 3: KAMAR DIBERSIHKAN (CLEANING) ===
    public function cleaningRooms()
    {
        // Filter kamar yang status dinamisnya 'Cleaning'
        // (Status ini didapat REAL dari database Transaction status='Cleaning')
        $rooms = Room::all()->filter(function ($room) {
            return $room->dynamic_status === 'Cleaning';
        });

        return view('room-info.cleaning', [
            'rooms' => $rooms,
            'title' => 'Kamar Sedang Dibersihkan'
        ]);
    }
}
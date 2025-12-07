<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    /**
     * Mengembalikan data JSON untuk Grafik Batang (Chart.js)
     */
    public function dailyGuestPerMonth()
    {
        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;

        // Buat array tanggal 1 sampai 30/31
        $days = collect(range(1, $daysInMonth));

        // Looping setiap hari untuk menghitung jumlah tamu
        $guests = $days
            ->map(function ($day) use ($currentDate) {
                // Panggil fungsi helper di bawah
                return $this->dailyTotalGuests($currentDate->year, $currentDate->month, $day);
            })
            ->toArray();

        // Tentukan batas atas grafik agar tampilan rapi
        // Jika data kosong, max minimal 10
        $maxData = max($guests);
        $max = $maxData == 0 ? 10 : (int) ceil(($maxData + 5) / 5) * 5;

        return [
            'day' => $days->toArray(),
            'guest_count_data' => $guests,
            'max' => $max,
        ];
    }

    /**
     * Menampilkan Detail Tamu saat Grafik diklik
     */
    public function dailyGuest(Request $request)
    {
        $date = Carbon::createFromDate(
            year: $request->year,
            month: $request->month,
            day: $request->day
        );

        // Ambil detail transaksi pada tanggal tersebut
        // Logic: Check In <= Tanggal Klik AND Check Out >= Tanggal Klik
        // Status: SEMUA KECUALI CANCEL (Agar history Check Out tetap muncul)
        $transactions = Transaction::with('user', 'room', 'customer')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>=', $date)
            ->where('status', '!=', 'Cancel') 
            ->get();

        return view('dashboard.chart_detail', [
            'transactions' => $transactions,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * Helper: Menghitung total tamu pada tanggal spesifik
     * INI KUNCI AGAR GRAFIK TIDAK LURUS/DATA HILANG
     */
    private function dailyTotalGuests($year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day);

        return Transaction::whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>=', $date)
            // KUNCI PERBAIKAN:
            // Jangan filter 'Check In' saja.
            // Filter '!= Cancel' agar tamu yang sudah 'Payment Success' tetap jadi balok grafik.
            ->where('status', '!=', 'Cancel') 
            ->count();
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class AutoCheckIn extends Command
{
    protected $signature = 'hotel:auto-checkin';
    protected $description = 'Otomatis pindahkan Reservasi hari ini ke Check In pada jam 12:00';

    public function handle()
    {
        // Pastikan menggunakan waktu Jakarta (WIB)
        // Agar sinkron dengan jam operasional hotel
        $now = Carbon::now('Asia/Jakarta');

        // Cek apakah sekarang sudah jam 12:00 WIB atau lebih
        if ($now->format('H') >= 12) {
            
            // Cari reservasi yang check_in nya HARI INI
            // Dan ubah statusnya menjadi 'Check In'
            $count = Transaction::where('status', 'Reservation')
                ->whereDate('check_in', Carbon::today('Asia/Jakarta'))
                ->update(['status' => 'Check In']);

            // Tampilkan info di log jika ada data yang berubah
            if ($count > 0) {
                $this->info("Berhasil memindahkan {$count} reservasi ke Check In.");
            }
        }
    }
}
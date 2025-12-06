<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class AutoMoveReservationToCheckin extends Command
{
    protected $signature = 'hotel:auto-checkin-move';
    protected $description = 'Pindahkan status Reservasi ke Check In pada jam 14:00 hari H';

    public function handle()
    {
        // Cek apakah sekarang sudah jam 14:00 atau lebih
        if (Carbon::now()->format('H') >= 14) {
            Transaction::where('status', 'Reservation')
                ->whereDate('check_in', Carbon::today()) // Hanya hari ini
                ->update(['status' => 'Check In']);
            
            $this->info('Data reservasi hari ini dipindahkan ke tabel Check In.');
        }
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class AutoCleanRoom extends Command
{
    protected $signature = 'hotel:auto-clean';
    protected $description = 'Otomatis ubah status Cleaning menjadi Done setelah 1 jam';

    public function handle()
    {
        // Cari kamar yang statusnya Cleaning DAN sudah lebih dari 1 jam sejak di-update (checkout)
        $count = Transaction::where('status', 'Cleaning')
            ->where('updated_at', '<=', Carbon::now()->subHour())
            ->update(['status' => 'Done']); // Done = Masuk Laporan / History

        $this->info("Berhasil menyelesaikan cleaning untuk {$count} kamar.");
    }
}
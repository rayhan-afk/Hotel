<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Tambah kolom paid_amount setelah total_price
            // Default 0 agar tidak error pada data baru
            $table->bigInteger('paid_amount')->default(0)->after('total_price');
        });

        // 2. [PENTING] Update data lama
        // Kita asumsikan transaksi yang sudah berjalan (Check In/Done) dianggap sudah lunas (paid_amount = total_price)
        // Agar sistem tidak mendeteksi mereka sebagai "Kurang Bayar"
        DB::statement("UPDATE transactions SET paid_amount = total_price WHERE status IN ('Check In', 'Done', 'Paid')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus kolom jika rollback
            $table->dropColumn('paid_amount');
        });
    }
};
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
        // Jalankan SQL Query untuk memperbaiki data yang "aneh" (Sisa Bayar = Total Harga)
        // Kita set paid_amount = total_price KHUSUS untuk tamu yang statusnya 'Check In' tapi paid_amount-nya masih 0
        DB::statement("UPDATE transactions SET paid_amount = total_price WHERE status = 'Check In' AND paid_amount = 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu melakukan apa-apa saat rollback, 
        // karena kita tidak ingin mengembalikan data menjadi salah (0) lagi.
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Cek apakah kolom 'count_person' BELUM ada? Kalau belum, buat.
            if (!Schema::hasColumn('transactions', 'count_person')) {
                // Taruh setelah customer_id biar rapi
                $table->integer('count_person')->default(1)->after('customer_id'); 
            }

            // 2. Cek apakah kolom 'count_child' BELUM ada? Kalau belum, buat.
            if (!Schema::hasColumn('transactions', 'count_child')) {
                $table->integer('count_child')->default(0)->after('count_person');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'count_person')) {
                $table->dropColumn('count_person');
            }
            if (Schema::hasColumn('transactions', 'count_child')) {
                $table->dropColumn('count_child');
            }
        });
    }
};
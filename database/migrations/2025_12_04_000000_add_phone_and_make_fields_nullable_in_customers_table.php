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
        Schema::table('customers', function (Blueprint $table) {
            // 1. Tambah kolom phone (nullable dulu untuk data lama, atau default kosong)
            $table->string('phone', 20)->nullable()->after('address'); 
            
            // 2. Ubah kolom birthdate jadi nullable (opsional)
            $table->date('birthdate')->nullable()->change();
            
            // 3. Ubah kolom email jadi nullable (opsional)
            // Note: Kolom email biasanya ada di tabel 'users', bukan 'customers' langsung jika relasinya 1-to-1.
            // Tapi jika Anda menyimpan email juga di tabel customers, gunakan baris di bawah.
            // Jika email diambil dari tabel 'users', kita skip yang ini.
            // Asumsi: email ada di tabel users, tapi mari kita cek modelnya nanti.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->date('birthdate')->nullable(false)->change(); // Kembalikan jadi wajib
        });
    }
};
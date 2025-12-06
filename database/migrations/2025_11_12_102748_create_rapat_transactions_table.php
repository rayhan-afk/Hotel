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
        Schema::create('rapat_transactions', function (Blueprint $table) {
            $table->id();

            // Kunci Asing (Foreign Keys)
            $table->foreignId('rapat_customer_id')->constrained('rapat_customers');
            $table->foreignId('ruang_rapat_paket_id')->constrained('ruang_rapat_pakets');

            // Data dari Step 2 (Data Reservasi)
            $table->date('tanggal_pemakaian');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('status_reservasi')->default('Pending'); // cth: Pending, Confirmed

            // Data dari Step 3 (Paket & Layanan)
            $table->integer('jumlah_peserta');

            // Data dari Step 4 (Informasi Biaya)
            $table->decimal('harga', 15, 2); // Harga total yang dihitung
            $table->decimal('total_pembayaran', 15, 2)->default(0);
            $table->string('status_pembayaran')->default('Unpaid'); // cth: Unpaid, Paid

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapat_transactions');
    }
};
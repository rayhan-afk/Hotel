<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruang_rapat_pakets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama Paket (cth: "Paket 1", "Paket Half Day")
            $table->text('isi_paket'); // Deskripsi isi paket
            $table->text('fasilitas'); // Fasilitas yang didapat
            $table->decimal('harga', 15, 2); // Harga paket
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruang_rapat_pakets');
    }
};

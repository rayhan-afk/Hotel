<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Pivot (Penghubung)
        Schema::create('amenity_room', function (Blueprint $table) {
            $table->id();
            
            // Kunci Asing ke tabel rooms
            $table->foreignId('room_id')
                  ->constrained('rooms')
                  ->onDelete('cascade'); // Kalau kamar dihapus, aturan jatahnya hilang

            // Kunci Asing ke tabel amenities
            $table->foreignId('amenity_id')
                  ->constrained('amenities')
                  ->onDelete('cascade'); // Kalau barang dihapus dari master, aturan hilang

            // INI KUNCINYA: Kolom Jumlah Jatah
            // Misal: room_id=1 (Kamar 101), amenity_id=5 (Sandal), amount=2
            $table->integer('amount')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_room');
    }
};
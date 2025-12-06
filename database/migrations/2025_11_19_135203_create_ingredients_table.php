<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Nama Bahan
            $table->string('category');              // Kategori (Sayuran, Daging, dll)
            $table->decimal('stock', 10, 2)->default(0); // Stok (Decimal biar bisa koma, misal 1.5 kg)
            $table->string('unit');                  // Satuan (Kg, Gram, Pcs)
            $table->text('description')->nullable(); // Keterangan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
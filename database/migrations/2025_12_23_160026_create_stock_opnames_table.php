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
        Schema::create('stock_opnames', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ingredient_id')->constrained()->onDelete('cascade'); // Relasi ke bahan
        $table->decimal('system_stock', 10, 2); // Stok di komputer sebelum opname
        $table->decimal('physical_stock', 10, 2); // Stok fisik hasil hitungan
        $table->decimal('difference', 10, 2); // Selisih (Fisik - Sistem)
        $table->text('notes')->nullable(); // Catatan (misal: "Barang rusak/hilang")
        $table->timestamps(); // Tanggal Opname
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};

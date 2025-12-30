<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenity_stock_opnames', function (Blueprint $table) {
            $table->id();
            // Hubungkan ke tabel amenities
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade'); 
            
            $table->decimal('system_stock', 10, 2);   // Stok di komputer
            $table->decimal('physical_stock', 10, 2); // Stok fisik
            $table->decimal('difference', 10, 2);     // Selisih
            $table->text('notes')->nullable();        // Catatan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_stock_opnames');
    }
};
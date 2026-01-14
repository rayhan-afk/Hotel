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
            
            // Relasi ke amenities
            $table->foreignId('amenity_id')
                  ->constrained('amenities')
                  ->onDelete('cascade'); 

            // Gunakan Bahasa Inggris
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference'); // Selisih
            
            $table->text('note')->nullable(); // Catatan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenity_stock_opnames');
    }
};
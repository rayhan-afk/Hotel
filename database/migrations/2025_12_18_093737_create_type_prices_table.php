<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('type_prices', function (Blueprint $table) {
        $table->id();
        
        // Relasi ke Tipe Kamar (Deluxe, Suite, dll)
        $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
        
        // Grup Customer (Harus sama tulisannya dengan yang di tabel customer)
        $table->string('customer_group'); 
        
        // Dua Harga Berbeda (Weekday vs Weekend)
        $table->decimal('price_weekday', 15, 2); // Harga Senin-Kamis/Minggu
        $table->decimal('price_weekend', 15, 2); // Harga Jumat-Sabtu
        
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('type_prices');
}
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Daftar Menu Makanan/Minuman
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: Nasi Goreng Spesial
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Foto makanan
            $table->decimal('price', 15, 2); // Harga jual
            $table->enum('category', ['Food', 'Beverage', 'Snack', 'Other'])->default('Food');
            $table->boolean('is_available')->default(true); // Untuk mematikan menu jika habis total
            $table->timestamps();
        });

        // 2. Tabel Pivot (Resep) - Menghubungkan Menu dengan Ingredient
        Schema::create('menu_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            
            // Pastikan tabel 'ingredients' sudah ada sebelumnya (sesuai struktur file kamu)
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            
            // Jumlah bahan yang dipakai per 1 porsi menu
            // Pastikan satuannya SAMA dengan yang di tabel ingredients (misal: gram atau mililiter)
            $table->decimal('quantity_needed', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_ingredients');
        Schema::dropIfExists('menus');
    }
};
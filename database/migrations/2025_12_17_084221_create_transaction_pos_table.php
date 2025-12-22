<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Transaksi POS (Header Nota)
        Schema::create('transaction_pos', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Contoh: POS-20241216-001
            $table->integer('total_amount');
            $table->integer('pay_amount'); // Uang yang dibayar
            $table->integer('change_amount'); // Kembalian
            $table->string('payment_method')->default('Tunai');
            $table->timestamps();
        });

        // 2. Tabel Detail Transaksi POS (Isi Nota)
        Schema::create('transaction_pos_details', function (Blueprint $table) {
            $table->id();
            // Hubungkan ke tabel 'transaction_pos' (bukan 'transactions' biasa)
            $table->foreignId('transaction_id')->constrained('transaction_pos')->onDelete('cascade');
            
            $table->foreignId('menu_id')->constrained('menus'); // Hubungkan ke tabel menus
            $table->integer('qty');
            $table->integer('price'); // Harga saat transaksi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_pos_details');
        Schema::dropIfExists('transaction_pos');
    }
};
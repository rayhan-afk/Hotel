<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();     // Admin/Resepsionis yang input
            $table->foreignId('customer_id')->constrained(); // Tamu
            $table->foreignId('room_id')->constrained();     // Kamar
            
            // [PERBAIKAN 1] Ganti date() jadi dateTime() agar mencatat JAM & MENIT
            // Ini wajib supaya fitur timer 1 jam cleaning bisa jalan.
            $table->dateTime('check_in'); 
            $table->dateTime('check_out');
            
            $table->string('status'); // Reservation, Check In, Cleaning, Done, Cancel
            
            // [PERBAIKAN 2] Tambahkan kolom yang hilang tapi dipakai di Controller
            $table->bigInteger('total_price')->default(0); 
            $table->string('breakfast')->default('No'); // Yes/No
            
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
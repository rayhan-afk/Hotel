<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTableRevised extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            
            // KOLOM ASLI
            $table->foreignId('type_id')->constrained();
            $table->string('number');
            $table->bigInteger('capacity');
            $table->double('price');
            
            // KOLOM BARU
            $table->string('name');
            $table->double('area_sqm')->nullable();
            
            // === [INI YANG HILANG SEBELUMNYA] ===
            $table->string('breakfast')->default('No'); 
            // ====================================

            $table->longText('room_facilities')->nullable();
            $table->longText('bathroom_facilities')->nullable();
            $table->string('main_image_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
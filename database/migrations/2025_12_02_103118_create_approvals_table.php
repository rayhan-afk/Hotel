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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();

            // type: contoh -> 'type', 'ruang_rapat_paket'
            $table->string('type')->nullable();

            // ID data yang ingin di-approve
            $table->unsignedBigInteger('reference_id');

            // siapa yang request approval
            $table->unsignedBigInteger('requested_by');

            // data lama dan baru dalam format JSON
            $table->json('old_data');
            $table->json('new_data');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // yang menyetujui
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
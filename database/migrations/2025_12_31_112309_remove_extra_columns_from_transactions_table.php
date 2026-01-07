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
        Schema::table('transactions', function (Blueprint $table) {
            // Menghapus kolom extra_bed dan extra_breakfast
            $table->dropColumn(['extra_bed', 'extra_breakfast']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Mengembalikan kolom jika di-rollback
            $table->integer('extra_bed')->default(0)->nullable();
            $table->integer('extra_breakfast')->default(0)->nullable();
        });
    }
};
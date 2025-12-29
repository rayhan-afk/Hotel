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
    Schema::table('transactions', function (Blueprint $table) {
        // Ubah dari DATE (2025-12-25) menjadi DATETIME (2025-12-25 14:30:00)
        $table->dateTime('check_in')->change();
        $table->dateTime('check_out')->change();
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->date('check_in')->change();
        $table->date('check_out')->change();
    });
}
};

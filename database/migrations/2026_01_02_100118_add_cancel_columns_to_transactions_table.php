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
            $table->string('cancel_reason')->nullable()->after('status');
            $table->text('cancel_notes')->nullable()->after('cancel_reason');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancel_notes']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerGroupToCustomersTable extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Kita taruh setelah kolom 'job' biar rapi
            $table->string('customer_group')
                  ->default('General') // Default 'General' biar user lama aman
                  ->after('job') 
                  ->comment('General, Corporate, Family, Government');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_group');
        });
    }
}
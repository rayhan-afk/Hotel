<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Header Order (Satu struk pembayaran)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-RESTO-2025...
            
            // User yang menginput (Kasir/Waiter)
            $table->foreignId('user_id')->constrained('users'); 
            
            // Opsional: Jika dibebankan ke kamar tamu (Room Service)
            $table->foreignId('customer_id')->nullable()->constrained('customers'); 
            
            $table->string('table_number')->nullable(); // Nomor Meja
            $table->decimal('total_price', 15, 2)->default(0);
            
            // Status Order: Pending -> Cooking -> Served -> Paid
            $table->enum('status', ['Pending', 'Cooking', 'Served', 'Completed', 'Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Unpaid', 'Paid', 'Charge to Room'])->default('Unpaid');
            
            $table->timestamps();
        });

        // 2. Tabel Detail Item Order (Apa saja yang dipesan)
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus');
            
            $table->integer('qty'); // Jumlah porsi
            $table->decimal('price', 15, 2); // Harga saat transaksi (snapshot)
            $table->decimal('subtotal', 15, 2); // qty * price
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
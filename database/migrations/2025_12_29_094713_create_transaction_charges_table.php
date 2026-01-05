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
        Schema::create('transaction_charges', function (Blueprint $table) {
            $table->id();
            
            // 1. Relasi ke Tabel Transactions
            // onDelete('cascade') artinya kalau transaksi dihapus, charge-nya ikut terhapus
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');

            // 2. Kategori Sales (Sesuai list yang kamu minta)
            $table->enum('type', [
                'Room Payment',      // Pembayaran Kamar Manual (Jaga-jaga)
                'Laundry',           // Laundry
                'Deposit',           // Deposit
                'Room Service',      // Makanan/Minuman
                'Transportation',    // Antar Jemput
                'Lost and Breakage', // Denda Kerusakan/Kehilangan
                'Miscellaneous'      // Lain-lain
            ]);

            // 3. Detail Item
            $table->string('item_name');        // Contoh: "Nasi Goreng" atau "Cuci Jas"
            $table->integer('qty')->default(1); // Jumlah Barang
            $table->double('amount');           // Harga Satuan
            $table->double('total');            // Total Harga (Qty * Amount)
            
            // 4. Catatan Tambahan (Opsional)
            $table->text('note')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_charges');
    }
};
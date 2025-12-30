<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    // INI KUNCINYA: Izinkan semua kolom diisi (kecuali ID)
    // Kalau ini tidak ada, Laravel menolak menyimpan data riwayat, dan transaksi dibatalkan.
    protected $guarded = ['id']; 
    
    // Atau kalau mau manual satu per satu (pilih salah satu cara):
    // protected $fillable = ['ingredient_id', 'system_stock', 'physical_stock', 'difference', 'notes'];

    // Relasi ke Ingredient (Opsional tapi bagus ada)
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
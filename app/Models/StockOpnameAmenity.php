<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameAmenity extends Model
{
    use HasFactory;

    // 1. Sesuaikan nama tabel di database kamu
    // (Cek di phpMyAdmin, apakah namanya 'stock_opname_amenities' atau yang lain?)
    protected $table = 'amenity_stock_opnames'; 

    // 2. Kolom yang boleh diisi (Sesuaikan dengan kolom database kamu)
    protected $fillable = [
        'amenity_id', // ID barang
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
        'created_at' // Jika perlu input manual tanggal
    ];

    /**
     * Relasi ke Model Amenity (Barang)
     * Ini PENTING agar fungsi "with('amenity')" di controller jalan.
     */
    public function amenity()
    {
        // Pastikan model 'Amenity' ada di app/Models/Amenity.php
        // 'amenity_id' adalah foreign key di tabel stock opname
        return $this->belongsTo(Amenity::class, 'amenity_id');
    }
}
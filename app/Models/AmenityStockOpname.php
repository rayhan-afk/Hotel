<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmenityStockOpname extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke tabel amenities (opsional, buat jaga-jaga kalau butuh join nanti)
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }
}
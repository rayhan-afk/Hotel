<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    // Izinkan semua field diisi kecuali ID
    protected $guarded = ['id'];

    // Relasi kebalikannya (Opsional, tapi bagus ada)
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'amenity_room')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
}
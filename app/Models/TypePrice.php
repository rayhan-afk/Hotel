<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'customer_group',
        'price_weekday',
        'price_weekend'
    ];

    // Relasi balik ke Tipe Kamar
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
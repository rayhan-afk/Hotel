<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = []; // Biar semua kolom bisa diisi (mass assignment)

    // Relasi: Order punya banyak detail item
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi: Order milik User (Kasir)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
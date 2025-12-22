<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi: Detail ini milik order mana?
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Detail ini menu apa?
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
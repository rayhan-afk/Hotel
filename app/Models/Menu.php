<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke Ingredients (Resep)
    public function ingredients()
    {

        
        // withPivot penting agar kita bisa ambil kolom 'quantity_needed'
        return $this->belongsToMany(Ingredient::class, 'menu_ingredients')
                    ->withPivot('quantity_needed')
                    ->withTimestamps();
    }
}
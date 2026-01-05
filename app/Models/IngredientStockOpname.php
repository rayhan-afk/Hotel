<?php

namespace App\Models; // <--- INI KUNCINYA (Harus App\Models)
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientStockOpname extends Model
{
    use HasFactory;

    protected $table = 'stock_opnames'; // Koneksi ke tabel yang sama

    protected $fillable = [
        'ingredient_id', 
        'system_stock',
        'physical_stock',
        'difference',
        'notes',
    ];

    public function dataBahan()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
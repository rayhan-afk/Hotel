<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameAmenity extends Model
{
    use HasFactory;

    protected $table = 'amenity_stock_opnames'; 

    protected $fillable = [
        'amenity_id',
        'system_stock',   // English
        'physical_stock', // English
        'difference',     // English
        'note',           // English
        'created_at'
    ];

    public function amenity()
    {
        return $this->belongsTo(Amenity::class, 'amenity_id');
    }
}
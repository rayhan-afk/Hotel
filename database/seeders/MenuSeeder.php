<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// TAMBAHKAN DUA BARIS INI:
use App\Models\Menu;       
use App\Models\Ingredient; 

class MenuSeeder extends Seeder
{
    public function run()
    {
        // ... (kode di bawahnya tetap sama)
        
        // 1. Buat Bahan Baku (Ingredients)
        $beras = Ingredient::create([
            'name' => 'Beras Premium',
            'stock' => 5000, 
            'unit' => 'gram',
            'category' => 'Bahan Pokok'
        ]);

        $telur = Ingredient::create([
            'name' => 'Telur Ayam',
            'stock' => 100, 
            'unit' => 'pcs',
            'category' => 'Bahan Pokok'
        ]);

        $bawang = Ingredient::create([
            'name' => 'Bawang Merah',
            'stock' => 1000, 
            'unit' => 'gram',
            'category' => 'Sayur'
        ]);

    }
}
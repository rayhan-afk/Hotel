<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('amenities')->insert([
            [
                'nama_barang' => 'Handuk Mandi',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Handuk mandi untuk tamu hotel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Keset Kamar Mandi',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Keset lantai kamar mandi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Perlengkapan Gigi',
                'satuan' => 'set',
                'stok' => 50,
                'keterangan' => 'Sikat gigi dan pasta gigi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Penutup Kepala Mandi',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Shower cap sekali pakai',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Sisir',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Sisir tamu hotel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Sanitary Bag',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Sanitary bag di kamar mandi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Sabun Cair',
                'satuan' => 'liter',
                'stok' => 50,
                'keterangan' => 'Sabun mandi cair dengan botol dispenser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Sampo Cair',
                'satuan' => 'liter',
                'stok' => 50,
                'keterangan' => 'Sampo cair dengan botol dispenser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Sabun Cuci Tangan Cair',
                'satuan' => 'liter',
                'stok' => 50,
                'keterangan' => 'Hand soap cair dengan botol dispenser',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_barang' => 'Gelas',
                'satuan' => 'pcs',
                'stok' => 50,
                'keterangan' => 'Gelas untuk kamar tamu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangRapatPaket extends Model
{
    use HasFactory;

    protected $table = 'ruang_rapat_pakets'; // Pastikan nama tabel sesuai

    protected $fillable = [
        'name',
        'isi_paket',
        'fasilitas',
        'harga',
    ];
}
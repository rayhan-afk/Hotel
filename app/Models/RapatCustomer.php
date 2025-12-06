<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapatCustomer extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'rapat_customers';

    /**
     * Kolom yang boleh diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'nama',
        'no_hp',
        'email',
        'instansi',
    ];

    /**
     * Mendefinisikan relasi: Satu Customer Rapat bisa punya BANYAK Transaksi Rapat.
     */
    public function rapatTransactions()
    {
        return $this->hasMany(RapatTransaction::class);
    }
}
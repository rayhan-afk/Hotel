<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPos extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'transaction_pos'; 
    
    /**
     * Mass assignment protection
     */
    protected $guarded = [];

    /**
     * Relasi ke detail transaksi
     * Foreign key: transaction_id (sesuai dengan struktur database Anda)
     */
    public function details()
    {
        return $this->hasMany(TransactionPosDetail::class, 'transaction_id', 'id');
    }
}
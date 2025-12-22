<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPosDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'transaction_pos_details';
    
    /**
     * Mass assignment protection
     */
    protected $guarded = [];

    /**
     * Relasi ke transaksi utama (parent)
     * Foreign key: transaction_id
     */
    public function transaction()
    {
        return $this->belongsTo(TransactionPos::class, 'transaction_id', 'id');
    }

    /**
     * Relasi ke menu
     * Foreign key: menu_id
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
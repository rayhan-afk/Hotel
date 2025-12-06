<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapatTransaction extends Model
{
    use HasFactory;

    /**
     * Tentukan nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'rapat_transactions';

    /**
     * Kolom yang boleh diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'rapat_customer_id',
        'ruang_rapat_paket_id',
        'tanggal_pemakaian',
        'waktu_mulai',
        'waktu_selesai',
        'status_reservasi',
        'jumlah_peserta',
        'harga',
        'total_pembayaran',
        'status_pembayaran',
    ];

    /**
     * Mendefinisikan relasi: Satu Transaksi Rapat DIMILIKI OLEH SATU Customer Rapat.
     */
    public function rapatCustomer()
    {
        return $this->belongsTo(RapatCustomer::class);
    }

    /**
     * Mendefinisikan relasi: Satu Transaksi Rapat MEMILIKI SATU Paket Ruang Rapat.
     * Kita juga mengambil model RuangRapatPaket yang sudah ada.
     */
    public function ruangRapatPaket()
    {
        return $this->belongsTo(RuangRapatPaket::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapatTransaction extends Model
{
    use HasFactory;

    protected $table = 'rapat_transactions';

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

    // =================================================================
    // 1. RELASI UTAMA (NAMA LENGKAP)
    // =================================================================
    
    // Relasi ke Customer (Gunakan FK eksplisit agar tidak salah tebak)
    public function rapatCustomer()
    {
        return $this->belongsTo(RapatCustomer::class, 'rapat_customer_id');
    }

    // Relasi ke Paket
    public function ruangRapatPaket()
    {
        return $this->belongsTo(RuangRapatPaket::class, 'ruang_rapat_paket_id');
    }

    // =================================================================
    // 2. ALIAS (SOLUSI ERROR "UNDEFINED RELATIONSHIP")
    // =================================================================
    
    /**
     * Ini ALIAS PENTING.
     * Jika Controller memanggil ->with('customer') atau View memanggil $data->customer->nama,
     * fungsi ini yang akan bekerja.
     */
    public function customer()
    {
        return $this->belongsTo(RapatCustomer::class, 'rapat_customer_id');
    }

    /**
     * Ini juga ALIAS.
     * Supaya bisa dipanggil $data->paket->name
     */
    public function paket()
    {
        return $this->belongsTo(RuangRapatPaket::class, 'ruang_rapat_paket_id');
    }
}
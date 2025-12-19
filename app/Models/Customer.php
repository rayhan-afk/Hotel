<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'job',
        'birthdate',
        'user_id',
        'gender',
        'customer_group',
    ];

    /**
     * Relasi ke model User (1 Customer dimiliki oleh 1 User/Akun)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * [BARU] Relasi ke model Transaction (Riwayat Reservasi)
     * 1 Customer bisa memiliki banyak Transaksi
     */
    public function transactions()
    {
        // Pastikan model 'Transaction' ada di App\Models\Transaction
        return $this->hasMany(Transaction::class);
    }
}
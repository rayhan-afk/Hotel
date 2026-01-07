<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'room_id',
        'check_in',
        'check_out',
        'status',
        'total_price',
        'breakfast',
        'paid_amount',
        // 'extra_bed',
        // 'extra_breakfast'
        'cancel_reason', 
        'cancel_notes',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_price' => 'double', // Opsional: Biar aman jadi angka desimal/float
        'paid_amount' => 'double',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relasi payment dihapus karena tabelnya sudah tidak ada

    public function getTotalPrice()
    {
        // Prioritaskan harga yang tersimpan di database (karena sudah fix termasuk breakfast)
        if ($this->total_price) {
            return $this->total_price;
        }

        // Fallback hitung manual jika data lama kosong
        $day = Helper::getDateDifference($this->check_in, $this->check_out);
        $room_price = $this->room->price;

        return $room_price * $day;
    }

    public function getDateDifferenceWithPlural()
    {
        $day = Helper::getDateDifference($this->check_in, $this->check_out);
        $plural = Str::plural('Day', $day);

        return $day.' '.$plural;
    }

    // getTotalPayment() DIHAPUS karena tabel payment tidak ada
    // getMinimumDownPayment() DIHAPUS
        public function charges()
    {
        return $this->hasMany(TransactionCharge::class);
    }

    // Helper untuk hitung total tagihan tambahan
    public function getTotalChargesAttribute()
    {
        return $this->charges->sum('total');
    }

    // Helper Total Grand Total (Kamar + Charges)
    public function getGrandTotalAllAttribute()
    {
        // total_price (Harga Kamar + Extra Bed dari logic sebelumnya) + total charges
        return $this->total_price + $this->total_charges; 
    }
}
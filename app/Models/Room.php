<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'number',
        'name',
        'status', // Pastikan kolom ini ada di fillable
        'capacity',
        'price',
        'area_sqm',
        'room_facilities',
        'bathroom_facilities',
        'main_image_path',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    // === RELASI UTAMA ===
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    // === RELASI STATUS ===
    
    // 1. Cek Tamu Sedang Menginap (Status: Check In)
    public function currentTransaction()
    {
        return $this->hasOne(Transaction::class, 'room_id')
                    ->where('status', 'Check In')
                    ->whereDate('check_in', '<=', Carbon::today())
                    ->whereDate('check_out', '>=', Carbon::today());
    }

    // 2. Cek Reservasi Mendatang (Status: Reservation)
    public function futureReservation()
    {
        return $this->hasOne(Transaction::class, 'room_id')
                    ->where('status', 'Reservation')
                    ->whereDate('check_in', '>', Carbon::now())
                    ->orderBy('check_in', 'ASC');
    }

    // === ATTRIBUTE STATUS DINAMIS (LOGIC DIPERBAIKI) ===
    
    public function getDynamicStatusAttribute()
    {
        // PRIORITAS 1: Cek Status Fisik Kamar di Database (Cleaning)
        // Kita cek kolom 'status' di tabel 'rooms' terlebih dahulu.
        // Jika sedang dibersihkan, maka statusnya 'Cleaning' apapun kondisi transaksinya.
        if ($this->status === 'Cleaning') {
            return 'Cleaning';
        }

        // PRIORITAS 2: Cek Tamu Sedang Menginap
        if ($this->currentTransaction) {
            return 'Occupied';
        }
        
        // PRIORITAS 3: Cek Reservasi Mendatang
        if ($this->futureReservation) {
            return 'Reserved';
        }

        // Default: Kosong & Bersih
        return 'Available';
    }

    public function firstImage()
    {
        if (!empty($this->main_image_path)) {
            return asset($this->main_image_path);
        }
        return asset('img/default/default-room.png');
    }

    // Tambahkan ini di App/Models/Room.php

    public function getImage()
    {
        // 1. Jika gambar kosong, kembalikan gambar default
        if (empty($this->image) && empty($this->main_image_path)) {
            return asset('img/default/default-room.png');
        }

        // Ambil nilai dari kolom (bisa 'image' atau 'main_image_path' tergantung DB Anda)
        $path = $this->image ?? $this->main_image_path;

        // 2. Jika path adalah URL lengkap (misal dari lorempixel/internet), langsung kembalikan
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // 3. PEMBERSIHAN PATH (KUNCI PERBAIKAN)
        // Hapus kata 'storage/' di depan jika sudah ada, biar tidak dobel nanti.
        // Jadi "storage/img/rooms/..." menjadi "img/rooms/..."
        $cleanPath = str_replace('storage/', '', $path);

        // 4. Cek apakah file fisik BENAR-BENAR ADA di storage?
        if (file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // 5. Cek apakah file ada di folder public biasa (untuk data seeder lama)
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // 6. Jika semua gagal, kembalikan default daripada error/broken image
        return asset('img/default/default-room.png');
    }
}
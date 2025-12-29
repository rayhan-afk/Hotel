<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // [PENTING] Tambahkan ini untuk Slug
use Carbon\Carbon;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'number',
        'name',
        'status',
        'capacity',
        'price',
        'area_sqm',
        'room_facilities',
        'bathroom_facilities',
        'main_image_path',
    ];

    /**
     * [BARU] Tambahkan atribut virtual agar otomatis muncul di JSON/Array
     * Ini berguna saat data dipanggil via AJAX/Datatable
     */
    protected $appends = ['image_url'];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // === ATTRIBUTE IMAGE URL (LOGIC PERBAIKAN UTAMA) ===
    
    /**
     * Accessor ini mengecek gambar di berbagai lokasi folder.
     * Dipanggil via: $room->image_url
     */
    public function getImageUrlAttribute()
    {
        // 1. Jika path di DB kosong, return default
        if (empty($this->main_image_path)) {
            return asset('img/default/default-room.png');
        }

        // 2. Jika path adalah URL lengkap (https://...), return langsung
        if (filter_var($this->main_image_path, FILTER_VALIDATE_URL)) {
            return $this->main_image_path;
        }

        // 3. [PRIORITAS UTAMA] Cek Folder Terstruktur: img/room/{id}-{slug}/{filename}
        // Ini agar konsisten dengan cara penyimpanan di Repository (jika pakai folder per room)
        $folderName = $this->id . '-' . Str::slug($this->name);
        $structuredPath = 'img/room/' . $folderName . '/' . $this->main_image_path;

        if (file_exists(public_path($structuredPath))) {
            return asset($structuredPath);
        }

        // 4. [FALLBACK 1] Cek path langsung di folder Public (misal DB simpan: "img/room/foto.jpg")
        // Kita bersihkan kata "storage/" jika ada, karena asset() sudah mengarah ke public
        $cleanPath = str_replace('storage/', '', $this->main_image_path);
        
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // 5. [FALLBACK 2] Cek di folder Storage Link (public/storage/...)
        if (file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // 6. Jika semua gagal (file fisik hilang), return default daripada error/broken image
        return asset('img/default/default-room.png');
    }

    /**
     * Helper untuk View lama agar tidak error.
     * Kita arahkan langsung ke logic baru di atas.
     */
    public function firstImage()
    {
        return $this->image;
    }

    // public function getImage()
    // {
    //     return $this->main_image_path;   //disini
    // }

    public function getImage()
    {
        return $this->image_url;
    }
    // === LOGIC STATUS ===

    public function currentTransaction()
    {
        return $this->hasOne(Transaction::class, 'room_id')
                    ->where('status', 'Check In')
                    ->whereDate('check_in', '<=', Carbon::today())
                    ->whereDate('check_out', '>=', Carbon::today());
    }

    public function futureReservation()
    {
        return $this->hasOne(Transaction::class, 'room_id')
                    ->where('status', 'Reservation')
                    ->whereDate('check_in', '>', Carbon::now())
                    ->orderBy('check_in', 'ASC');
    }

    public function getDynamicStatusAttribute()
    {
        // Prioritas 1: Sedang dibersihkan
        if ($this->status === 'Cleaning') {
            return 'Cleaning';
        }

        // Prioritas 2: Ada tamu check-in sekarang
        if ($this->currentTransaction) {
            return 'Occupied';
        }
        
        // Prioritas 3: Sudah di-booking untuk masa depan
        if ($this->futureReservation) {
            return 'Reserved';
        }

        // Default
        return 'Available';
    }

        public function amenities()
    {
        // Relasi Many-to-Many ke model Amenity
        // withPivot('amount') artinya kita mau ambil data jumlah jatahnya juga
        return $this->belongsToMany(Amenity::class, 'amenity_room')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
}
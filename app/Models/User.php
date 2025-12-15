<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'avatar',
        'password',
        'random_key',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * [BARU] Tambahkan atribut virtual ini agar muncul di JSON Datatable
     */
    protected $appends = ['avatar_url'];

    /**
     * [BARU] Accessor untuk membuat URL Avatar yang Benar & Konsisten
     * Format Folder: {id}-{slug_nama} (Contoh: 1-budi-santoso)
     * Ini dipanggil di JS via: row.user.avatar_url
     */
    public function getAvatarUrlAttribute()
    {
        if (! $this->avatar) {
            return asset('img/default/default-user.jpg');
        }

        // PENTING: Gunakan Str::slug agar sesuai dengan nama folder yang dibuat Repository
        $folderName = $this->id . '-' . Str::slug($this->name);

        return asset('img/user/' . $folderName . '/' . $this->avatar);
    }

    /**
     * [UPDATE] Helper untuk Blade View
     * Sekarang cukup panggil accessor di atas agar logic-nya satu pintu.
     */
    public function getAvatar()
    {
        return $this->avatar_url;
    }

    // ===================================
    // RELASI & ROLE CHECK
    // ===================================

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function isCustomer()
    {
        return $this->role === 'Customer'; 
    }

    public function isSuper()
    {
        return $this->role === 'Super';
    }

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isManager()
    {
        return $this->role === 'Manager';
    }

    public function isDapur()
    {
        return $this->role === 'Dapur';
    }

    /**
     * Fungsi ini untuk mengecek apakah user memiliki salah satu dari role yang diizinkan.
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($this->role, $roles);
    }
}
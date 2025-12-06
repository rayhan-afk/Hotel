<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function getAvatar()
    {
        if (! $this->avatar) {
            return asset('img/default/default-user.jpg');
        }

        return asset('img/user/'.$this->name.'-'.$this->id.'/'.$this->avatar);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function isCustomer()
    {
        // Pastikan penulisan 'Customer' sesuai dengan apa yang ada di Database (Besar/Kecil hurufnya)
        return $this->role === 'Customer'; 
    }
    // ===================================
    // 💡 LETAK PERUBAHAN UTAMA: PENAMBAHAN HELPER ROLE
    // ===================================
    
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
    
    // ===================================
    // 💡 AKHIR PERUBAHAN
    // ===================================

    /**
     * TAMBAHAN PENTING:
     * Fungsi ini untuk mengecek apakah user memiliki salah satu dari role yang diizinkan.
     * Bisa menerima input berupa String (satu role) atau Array (banyak role).
     */
    public function hasRole($roles)
    {
        // Jika input cuma 1 string (misal: "admin"), ubah jadi array dulu
        if (is_string($roles)) {
            $roles = [$roles];
        }

        // Cek apakah role user saat ini ada di dalam daftar role yang diperbolehkan
        // Contoh: Role user 'manager'. Apakah 'manager' ada di ['super', 'manager']? -> TRUE
        return in_array($this->role, $roles);

        
    }
    
}
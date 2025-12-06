<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Cek apakah user login (Safety Check)
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // ===================================
        // 👑 1. LOGIKA KHUSUS SUPERADMIN (GOD MODE)
        // ===================================
        // Tambahkan ini agar Superadmin SELALU lolos di semua route
        // tanpa perlu mendaftarkannya satu per satu di web.php
        if ($user->role === 'Super' || $user->role === 'Superadmin') {
            return $next($request);
        }

        // ===================================
        // 2. Logika Utama (Pengecekan Role Sesuai Parameter)
        // ===================================
        // Cek apakah role user ada di daftar yang diizinkan route
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // ===================================
        // 3. LOGIKA KHUSUS DAPUR
        // ===================================
        // Dapur hanya boleh mengakses rute yang mengandung kata 'ingredient'
        if ($user->isDapur()) {
            $routeName = Route::currentRouteName();
            
            if (str_contains($routeName, 'ingredient')) {
                return $next($request);
            }
            
            return abort(403, 'Anda tidak memiliki akses ke halaman ini. Dapur hanya dapat mengakses Bahan Baku.');
        }

        // 4. Penanganan Akses Ditolak
        return abort(403, 'Anda tidak memiliki akses ke halaman ini.'); 
    }
}
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
        if ($user->role === 'Super' || $user->role === 'Superadmin') {
            return $next($request);
        }

        // ===================================
        // 2. LOGIKA KHUSUS HOUSEKEEPING (Amenities & Kamar Dibersihkan)
        // ===================================
        if ($user->role === 'Housekeeping') {
            $routeName = Route::currentRouteName();
            
            // Izinkan HANYA amenity, room-info.cleaning, dan logout
            if (str_contains($routeName, 'amenity') || 
                $routeName === 'room-info.cleaning' || 
                $routeName === 'room-info.cleaning.finish' ||
                $routeName === 'logout' || 
                $routeName === 'logout.housekeeping') {
                return $next($request);
            }
            
            // Jika coba akses halaman lain, redirect ke amenities
            return redirect()->route('amenity.index')
                ->with('error', 'Anda hanya memiliki akses ke halaman Amenities dan Kamar Dibersihkan.');
        }

        // ===================================
        // 3. LOGIKA KHUSUS DAPUR (Ingredients Only)
        // ===================================
        if ($user->isDapur()) {
            $routeName = Route::currentRouteName();
            
            // Izinkan HANYA ingredient dan logout
            if (str_contains($routeName, 'ingredient') || $routeName === 'logout') {
                return $next($request);
            }
            
            // Jika coba akses halaman lain, redirect ke ingredients
            return redirect()->route('ingredient.index')
                ->with('error', 'Anda hanya memiliki akses ke halaman Bahan Baku.');
        }

        // ===================================
        // 4. Logika Utama (Pengecekan Role Sesuai Parameter)
        // ===================================
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 5. Penanganan Akses Ditolak
        return abort(403, 'Anda tidak memiliki akses ke halaman ini.'); 
    }
}
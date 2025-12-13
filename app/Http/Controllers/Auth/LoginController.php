<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // <--- Wajib di-import agar tidak error

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     * (Variabel ini akan diabaikan karena kita pakai function authenticated di bawah)
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     * Method ini akan memaksa redirect sesuai Role, mengabaikan 'intended url'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // 1. Cek Role Housekeeping
        // Pastikan di model User ada method isHousekeeping(), atau gunakan $user->role == 'Housekeeping'
        if ($user->role === 'Housekeeping' || (method_exists($user, 'isHousekeeping') && $user->isHousekeeping())) {
            return redirect()->route('amenity.index');
        }

        // 2. Cek Role Dapur
        if ($user->role === 'Dapur' || (method_exists($user, 'isDapur') && $user->isDapur())) {
            return redirect()->route('ingredient.index');
        }

        // 3. Sisanya (Super, Admin, Manager) ke Dashboard
        return redirect()->route('dashboard.index');
    }
}
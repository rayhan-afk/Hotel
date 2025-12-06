@extends('template.auth')
@section('title', 'Login')
@section('content')

    <style>
        body {
            background: linear-gradient(135deg, #C49A6C 0%, #50200C 100%);
            min-height: 100vh;
            font-family: 'humnst777';
            margin: 0;
            padding: 0;
            overflow: hidden; /* Mencegah scrollbar ganda */
        }

        .logo-img {
            max-height: 88px;
            width: auto;
            display: block;
        }

        /* --- CONTAINER UTAMA (FLEXBOX) --- */
        .login-container {
            min-height: 100vh;
            display: flex; /* Ini yang membuat layout menyamping */
            width: 100%;
        }

        /* --- BAGIAN 1: KIRI (GAMBAR) --- */
        .login-left {
            flex: 1.2; /* Lebar 60% (opsional, bisa diatur angkanya) */
            background: url("{{ asset('img/hotel-sawunggaling-1.png') }}");
            background-size: cover;       /* Default: Menutupi seluruh area, mungkin ada cropping */
            background-position: center;  /* Posisikan di tengah */
            background-repeat: no-repeat;
            position: relative;
        }

        /* Overlay gelap di atas gambar kiri agar lebih elegan */
        .login-left::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.3);
        }

        /* --- BAGIAN 2: KANAN (FORM) --- */
        .login-right {
            flex: 1; /* Lebar sisa */
            display: flex;
            align-items: center; /* Tengahkan vertikal */
            justify-content: center; /* Tengahkan horizontal */
            padding: 2rem;
            position: relative;
            background: transparent; /* Tidak ada background gambar disini */
        }

        /* KARTU LOGIN (Berada di dalam login-right) */
        .login-card {
            background: #C49A6C;
            backdrop-filter: blur(20px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px; /* Lebar maksimal kartu */
            animation: slideUp 0.6s ease-out;
            z-index: 10;
        }

        /* CSS LAINNYA TETAP SAMA SEPERTI SEBELUMNYA */
        .login-header {
            background: linear-gradient(135deg, #C49A6C 100%);
            color: white;
            text-align: center;
            padding: 3rem 2rem 2rem;
            position: relative;
        }

        .logo-container {
            width: 80px; height: 80px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }

        .login-body { padding: 2rem; }

        .form-floating { margin-bottom: 1rem; }
        
        .form-floating .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px; padding: 1rem;
            background: rgba(248, 250, 252, 0.8);
        }
        
        .form-floating .form-control:focus {
            border-color: #2563eb; background: white;
        }
        
        .form-floating label { color: #64748b; font-weight: 500; }

        .btn-login {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none; border-radius: 12px;
            padding: 1rem; color: white;
            font-weight: 600; width: 100%;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .btn-login:hover { transform: translateY(-2px); }

        /* Loading Spinner CSS */
        .btn-spinner {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 20px; height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white; border-radius: 50%;
            animation: spin 1s linear infinite;
            opacity: 0;
        }
        .btn-login.loading .btn-spinner { opacity: 1; }
        .btn-login.loading span { opacity: 0; }

        .divider { text-align: center; margin: 1rem 0; position: relative; }
        .divider::before {
            content: ''; position: absolute; top: 50%; left: 0; right: 0;
            height: 1px; background: #e2e8f0;
        }
        .divider span {
            background: #C49A6C; /* Sesuaikan dengan bg card */
            padding: 0 1rem; color: white; font-size: 0.875rem; position: relative;
        }

        .forgot-link { color: white; text-decoration: none; }
        
        /* --- TAMBAHAN: Style untuk Captcha --- */
        .captcha-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }

        

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* --- RESPONSIVE (PENTING) --- */
        @media (max-width: 992px) {
            .login-left { display: none; } /* Hilangkan gambar di layar kecil */
            .login-right { padding: 1rem; }
        }
        /* --- PERBAIKAN STABILITAS LAYOUT CAPTCHA ERROR (TAMBAHKAN INI) --- */
        .invalid-feedback {
            font-size: 0.875em; /* Ukuran teks standar Bootstrap */
            line-height: 1.2;
            padding: 0; /* Hapus padding bawaan */
            min-height: 1.5em;
        }

        /* Pastikan margin atas pesan error tidak terlalu besar, agar tombol tidak terdorong jauh */
        .invalid-feedback.d-block {
            display: block !important;
            /* PENTING: Mengatur margin atas agar tidak terlalu mendorong. */
            /* Ubah ini menjadi 0.25rem atau 0.5rem. Coba 0.5rem dulu. */
            margin-top: 0.5rem !important; 
            margin-bottom: 0 !important; /* Pastikan tidak ada margin bawah yang mendorong tombol */
        }
        </style>

    <div class="floating-shapes">
        <div class="shape" style="top: 10%; left: 10%; position: fixed; animation: float 6s infinite;">
            <img src="{{ asset('img/logo-anda.png') }}" style="width: 1rem;">
        </div>
    </div>
   
    <div class="login-container">
        
        <div class="login-left"></div>

        <div class="login-right">
            
            <div class="login-card">
                <div class="login-header">
                    <div class="logo-container">
                        <img src="{{ asset('img/logo-anda.png') }}" alt="Logo" class="logo-img"> 
                    </div>
                    <h4 class="mb-2 fw-bold">Hotel Sawunggaling</h4>
                    <p class="mb-0 opacity-75">Selamat Datang, Silahkan Login.</p>
                </div>

                <div class="login-body">
                    <form id="form-login" action="/login" method="POST">
                        @csrf

                        <div class="form-floating">
                            <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required autofocus>
                            <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        </div>

                        <div class="form-floating">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                            <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                        </div>

                        <div class="form-check mb-3"> 
                            <div class="form-group mb-3">
                                <label for="captcha" class="text-white mb-2">Kode Verifikasi</label>
                                <div class="d-flex align-items-center mb-3">
                                    {{-- Tag IMG Captcha Baru: Memanggil rute yang sudah dibuat di web.php --}}
                                    <img src="{{ route('captcha.generate') }}" alt="Kode Verifikasi" id="captcha-img" 
                                        style="border: 2px solid #e2e8f0; border-radius: 12px;">
                                    
                                    {{-- Tombol Reload --}}
                                    <button type="button" class="btn btn-secondary ml-2" 
                                            onclick="document.getElementById('captcha-img').src = '{{ route('captcha.generate') }}?'+Math.random()">
                                        &#x21bb; Reload
                                    </button>
                                </div>
                                
                                <div class="form-floating">
                                    <input id="captcha" type="text" class="form-control @error('captcha') is-invalid @enderror" 
                                        placeholder="Masukkan Kode di Gambar (Kapital)" name="captcha" required>
                                    <label for="captcha"><i class="fas fa-shield-alt me-2"></i>Masukkan Kode Keamanan</label>
                                </div>
                                <div class="error-placeholder">
                                @error('captcha')
                                    <span class="invalid-feedback d-block mt-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-white" for="remember">Remember me</label>
                        </div>

                        <button id="btn_submit" class="btn btn-login" type="submit">
                            <span id="text_submit"><i class="fas fa-sign-in-alt me-2"></i>Sign In</span>
                            <div class="btn-spinner"></div>
                        </button>

                        <div class="divider"><span>Need help?</span></div>

                        <div class="text-center">
                            <a href="/forgot-password" class="forgot-link">Forgot password?</a>
                        </div>
                    </form>
                </div>
            </div> </div> </div> <script>
        // Script JS tetap sama
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-login');
            const btn = document.getElementById('btn_submit');
            if(form){
                form.addEventListener('submit', function() {
                    btn.classList.add('loading');
                    btn.disabled = true;
                });
            }
        });
    </script>
@endsection
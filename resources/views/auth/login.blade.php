@extends('template.auth')
@section('title', 'Login - Hotel Sawunggaling')
@section('content')

{{-- Font Google --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">

<style>
    /* === KONFIGURASI WARNA & FONT === */
    :root {
        --color-cream: #F7F3E4;       
        --color-cream-dark: #EAE4D0;  
        --color-brown: #50200C;       
        --color-brown-light: #8D6E63; 
        --color-gold: #C49A6C;        
        --radius-xl: 30px;            
        --radius-md: 16px;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--color-cream);
        overflow-x: hidden;
    }

    /* --- LAYOUT UTAMA --- */
    .login-wrapper {
        min-height: 100vh;
        width: 100%;
        background-color: var(--color-cream);
    }

    /* --- BAGIAN SAMPING (GAMBAR) --- */
    .side-image-container {
        position: relative;
        min-height: 100vh;
        background-image: url("{{ asset('img/hotel-sawunggaling-1.png') }}");
        background-size: cover;
        background-position: center;
        border-top-right-radius: var(--radius-xl);
        border-bottom-right-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 10px 0 30px rgba(80, 32, 12, 0.1);
    }

    .side-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(80, 32, 12, 0.3) 0%, rgba(80, 32, 12, 0.8) 100%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 4rem;
        color: white;
    }

    .brand-text h1 {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.1;
        text-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .brand-text p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 80%;
        font-weight: 300;
        letter-spacing: 0.5px;
    }

    /* --- BAGIAN FORM --- */
    .form-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 2rem;
        background-color: var(--color-cream);
    }

    .form-card {
        width: 100%;
        max-width: 480px;
        padding: 1rem;
    }

    /* --- HEADER FORM (LOGO DOMINAN) --- */
    
    /* 1. Logo Lebih Besar */
    .logo-img {
        height: 140px; /* Ukuran Besar */
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 8px 16px rgba(80, 32, 12, 0.15));
        transition: transform 0.3s ease;
    }
    
    .logo-img:hover {
        transform: scale(1.05);
    }

    /* 2. Judul Lebih Kecil & Elegan */
    .welcome-title {
        font-family: 'Playfair Display', serif;
        color: var(--color-brown);
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1.5rem; /* Ukuran font dikecilkan agar logo menonjol */
        letter-spacing: 0.5px;
    }

    .welcome-subtitle {
        color: var(--color-brown-light);
        margin-bottom: 3rem;
        font-size: 0.95rem;
    }

    /* --- FORM INPUT STYLING --- */
    .form-floating > .form-control {
        background-color: #FFF;
        border: 1px solid #D7CCC8;
        border-radius: var(--radius-md);
        height: 3.5rem;
        padding-top: 1.625rem;
        font-size: 1rem;
        color: var(--color-brown);
        box-shadow: 0 4px 10px rgba(80, 32, 12, 0.03);
    }

    .form-floating > .form-control:focus {
        border-color: var(--color-brown);
        box-shadow: 0 0 0 4px rgba(196, 154, 108, 0.2); 
    }

    .form-floating > label {
        color: var(--color-brown-light);
        padding-top: 0.6rem;
        font-size: 0.9rem;
    }

    .password-toggle {
        cursor: pointer;
        color: var(--color-brown-light);
        transition: color 0.3s;
        z-index: 10;
        padding: 10px;
    }
    .password-toggle:hover { color: var(--color-brown); }

    /* --- CAPTCHA BOX --- */
    .captcha-container {
        background-color: #FFF;
        border: 1px dashed #D7CCC8;
        border-radius: var(--radius-md);
        padding: 15px;
        box-shadow: 0 2px 5px rgba(80, 32, 12, 0.03);
    }

    .captcha-input {
        letter-spacing: 3px;
        font-weight: 700;
        color: var(--color-brown);
        text-align: center;
        background-color: var(--color-cream);
        border: none;
    }
    
    .captcha-input:focus {
        background-color: #FFF;
        box-shadow: 0 0 0 2px var(--color-gold);
    }

    /* --- TOMBOL LOGIN --- */
    .btn-login {
        background: var(--color-brown);
        color: white;
        padding: 16px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 10px 20px rgba(80, 32, 12, 0.2);
    }

    .btn-login:hover {
        background-color: #3E1809;
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(80, 32, 12, 0.3);
        color: #fff;
    }

    .btn-login:disabled {
        background-color: #A1887F;
        transform: none;
    }

    .form-check-input:checked {
        background-color: var(--color-brown);
        border-color: var(--color-brown);
    }
    
    .link-forgot {
        color: var(--color-brown);
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.2s;
    }
    .link-forgot:hover { color: var(--color-gold); }

    .footer-copy {
        color: var(--color-brown-light);
        font-size: 0.85rem;
        margin-top: 2rem;
    }

    @media (max-width: 991.98px) {
        .side-image-container { display: none; }
        .form-panel { 
            background-color: var(--color-cream); 
            padding: 1.5rem;
        }
    }
    
    .fade-in-up { animation: fadeInUp 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-fluid p-0 login-wrapper">
    <div class="row g-0 h-100">
        
        <div class="col-lg-7 d-none d-lg-block side-image-container">
            <div class="side-overlay fade-in-up">
                <div class="brand-text">
                    <h1>Experience Luxury <br>at Sawunggaling</h1>
                    <p>Sistem Manajemen Hotel Terintegrasi dengan sentuhan warisan budaya dan kenyamanan modern.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-5 col-12 form-panel">
            <div class="form-card fade-in-up" style="animation-delay: 0.2s;">
                
                {{-- HEADER CENTERED --}}
                <div class="text-center">
                    <h2 class="welcome-title">Selamat Datang di</h2>
                    <img src="{{ asset('img/logo-anda.png') }}" alt="Logo Hotel" class="logo-img">
                    <p class="welcome-subtitle">Silakan masuk untuk mengelola hotel Anda.</p>
                </div>

                <form id="form-login" action="/login" method="POST">
                    @csrf

                    <div class="form-floating mb-4">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        <label for="email">Alamat Email</label>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required style="padding-right: 50px;">
                        <label for="password">Kata Sandi</label>
                        
                        <span id="togglePassword" class="password-toggle position-absolute top-50 end-0 translate-middle-y me-3">
                            <i class="fas fa-eye fa-lg"></i>
                        </span>
                    </div>

                    <div class="captcha-container mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="small fw-bold text-uppercase" style="color: var(--color-brown-light); font-size: 0.75rem;">Security Check</label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" 
                                    style="color: var(--color-gold);"
                                    onclick="document.getElementById('captcha-img').src = '{{ route('captcha.generate') }}?'+Math.random()"
                                    title="Reload Captcha">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                        
                        <div class="row g-2 align-items-center">
                            <div class="col-5">
                                <div class="bg-light rounded p-1 text-center border">
                                    <img src="{{ route('captcha.generate') }}" alt="Captcha" id="captcha-img" class="img-fluid rounded" style="max-height: 45px;">
                                </div>
                            </div>
                            <div class="col-7">
                                <input id="captcha" type="text" class="form-control captcha-input" 
                                       placeholder="Kode" name="captcha" required>
                            </div>
                        </div>
                        @error('captcha')
                            <div class="text-danger small mt-2 fw-semibold">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label small" for="remember" style="color: var(--color-brown);">
                                Ingat Saya
                            </label>
                        </div>
                        <!-- <a href="/forgot-password" class="link-forgot text-decoration-none small">
                            Lupa Kata Sandi?
                        </a> -->
                    </div>

                    <button id="btn_submit" type="submit" class="btn btn-login w-100 mb-4">
                        <span id="text_submit">Masuk ke Dashboard</span>
                        <div class="spinner-border spinner-border-sm text-white d-none" id="loader_submit" role="status"></div>
                    </button>
                    
                    <div class="text-center footer-copy">
                        <p class="mb-0">&copy; {{ date('Y') }} Hotel Sawunggaling. All rights reserved.</p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-login');
        const btn = document.getElementById('btn_submit');
        const textSubmit = document.getElementById('text_submit');
        const loader = document.getElementById('loader_submit');

        if(form){
            form.addEventListener('submit', function() {
                if(form.checkValidity()){
                    btn.disabled = true;
                    textSubmit.classList.add('d-none');
                    loader.classList.remove('d-none');
                }
            });
        }

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    });
</script>

@endsection
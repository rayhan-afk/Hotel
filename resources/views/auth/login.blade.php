@extends('template.auth')
@section('title', 'Login - Hotel Sawunggaling')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-gold: #C49A6C;
        --primary-dark: #50200C;
        --text-grey: #64748b;
        --bg-input: #f8fafc;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: #C49A6C;
        overflow-x: hidden;
    }

    /* --- LAYOUT UTAMA --- */
    .login-wrapper {
        min-height: 100vh;
        width: 100%;
    }

    /* BAGIAN KIRI (GAMBAR) */
    .side-image {
        background-image: url("{{ asset('img/Hotel.jpeg') }}");
        background-size: cover;
        background-position: center;
        position: relative;
        min-height: 100vh;
    }

    /* Overlay Gradient Mewah di Kiri */
    .side-image::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, rgba(80, 32, 12, 0.2), rgba(80, 32, 12, 0.8));
    }

    .side-content {
        position: absolute;
        bottom: 8%;
        left: 8%;
        right: 8%;
        color: white;
        z-index: 2;
    }

   
    .right-panel {
        
        height: 100vh; 
        padding: 1.5rem; 
        display: flex;
        flex-direction: column;
    }

    /* 2. AREA FORM (Kotak Emas Besar) */
    .form-wrapper {
        background: var(--primary-gold);
        width: 100%;
        height: 100%; 
        border-radius: 30px;
        display: flex;
        flex-direction: column;
        justify-content: center; 
        padding: 3rem 4rem; 
        overflow-y: auto; 
        box-shadow: 0 20px 40px rgba(80, 32, 12, 0.15);
    }
    
    /* RESPONSIVE: Agar di HP paddingnya tidak terlalu tebal */
    @media (max-width: 768px) {
        .right-panel {
            padding: 1rem; /* Rongga lebih kecil di HP */
            height: auto;
            min-height: 100vh;
        }
        .form-wrapper {
            padding: 2rem;
            height: auto;
            min-height: 85vh; /* Di HP biar tetap panjang tapi scrollable */
        }
    }
    /* TYPOGRAPHY */
    h1, h2, h3, h4, .serif-font {
        font-family: 'Playfair Display', serif;
    }

    /* INPUT FIELDS CUSTOM */
    .form-floating > .form-control {
        background-color: var(--bg-input);
        border: 1px solid #C49A6C;
        border-radius: 20px;
        height: 3.5rem;
        padding-top: 1.625rem;
        font-size: 0.95rem;
    }

    .form-floating > .form-control:focus {
        background-color: #fff;
        border-color: var(--primary-gold);
        box-shadow: 0 0 0 4px rgba(196, 154, 108, 0.1);
    }

    .form-floating > label {
        color: var(--text-grey);
        padding-top: 0.6rem;
    }

    /* TOMBOL LOGIN */
    .btn-login {
        background-color: var(--primary-dark);
        color: white;
        padding: 14px;
        border-radius: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }

    .btn-login:hover {
        background-color: var(--primary-dark);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -10px rgba(10, 5, 0, 0.5);
    }

    /* CAPTCHA STYLING */
    .captcha-box {
        background: var(--bg-input);
        padding: 15px;
        border-radius: 8px;
        border: 1px dashed #cbd5e1;
    }
    
    .captcha-img-container img {
        height: 50px;
        border-radius: 6px;
    }

    /* ANIMASI MASUK */
    .animate-up {
        animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Loading Spinner */
    .spinner-border-sm {
        width: 1.2rem; height: 1.2rem;
    }

    /* RESPONSIVE - MODIFIED */
    @media (max-width: 991.98px) {
        .side-image { display: none; }
        .form-wrapper { 
            padding: 2rem;
            border-radius: 0; /* Menghapus border radius pada tampilan mobile */
        }
    }
</style>

<div class="container-fluid p-0">
    <div class="row g-0 login-wrapper">
        
        <div class="col-lg-7 d-none d-lg-block side-image">
            <div class="side-content animate-up">
                <h2 class="display-5 fw-bold mb-3">Experience Luxury <br>at Sawunggaling</h2>
                <p class="lead opacity-75">Sistem Manajemen Hotel Terintegrasi untuk pelayanan yang lebih baik.</p>
            </div>
        </div>

        <div class="col-lg-5 col-12 right-panel">
            <div class="form-wrapper animate-up">
                
                <div class="mb-5 text-center text-lg-start">
                    <img src="{{ asset('img/logo-anda.png') }}" alt="Logo" height="60" class="mb-4">
                    <h3 class="fw-bold text-dark mb-1">Selamat Datang Kembali!!!</h3>
                    <p class="text-muted">Tolong Isi Username dan Password Untuk Masuk!!!</p>
                </div>

                <form id="form-login" action="/login" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                        <label for="email">Alamat Email</label>
                    </div>

                    <div class="form-floating mb-4 position-relative">
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Password" required style="padding-right: 50px;">
                        <label for="password">Kata Sandi</label>
                        
                        <span id="togglePassword" class="position-absolute top-50 end-0 translate-middle-y me-3" 
                              style="cursor: pointer; color: var(--text-grey); padding: 5px;">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>

                    <div class="captcha-box mb-4">
                        <label class="small text-muted fw-bold text-uppercase mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">Security Check</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-auto captcha-img-container">
                                <img src="{{ route('captcha.generate') }}" alt="Captcha" id="captcha-img">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="document.getElementById('captcha-img').src = '{{ route('captcha.generate') }}?'+Math.random()"
                                        title="Reload Captcha">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                            <div class="col">
                                <input id="captcha" type="text" 
                                       class="form-control form-control-sm border-0 bg-white shadow-none" 
                                       placeholder="Type code here..." 
                                       name="captcha" required 
                                       style="font-family: monospace; letter-spacing: 2px; font-weight: bold;">
                            </div>
                        </div>
                        @error('captcha')
                            <div class="text-danger small mt-2">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-muted small" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="/forgot-password" class="text-decoration-none small fw-bold" style="color: var(--primary-gold);">
                            Forgot Password?
                        </a>
                    </div>

                    <button id="btn_submit" type="submit" class="btn btn-login w-100 mb-4">
                        <span id="text_submit">Sign In to Dashboard</span>
                        <div class="spinner-border spinner-border-sm text-white d-none" id="loader_submit" role="status"></div>
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted small mb-0">&copy; {{ date('Y') }} Hotel Sawunggaling. All rights reserved.</p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Logic Loading Button
        const form = document.getElementById('form-login');
        const btn = document.getElementById('btn_submit');
        const textSubmit = document.getElementById('text_submit');
        const loader = document.getElementById('loader_submit');

        if(form){
            form.addEventListener('submit', function() {
                btn.disabled = true;
                textSubmit.classList.add('d-none');
                loader.classList.remove('d-none');
            });
        }

        // 2. Logic Show/Hide Password
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
<div class="top-shield"></div>

<nav class="navbar modern-navbar d-flex justify-content-between align-items-center">
    
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.index') }}">
        <img src="{{ asset('img/logo-anda.png') }}" alt="Hotel Logo" class="brand-logo-img">
    </a>

    <div class="clock-widget text-end">
        <div class="clock-display">
            <i class="fas fa-clock me-2 clock-icon"></i>
            <span id="clock-time">00:00:00</span>
            <span class="clock-zone">WIB</span>
        </div>
        <div class="date-text" id="clock-date">
            Senin, 01 Januari 2026
        </div>
    </div>
</nav>

<style>
    /* === KONFIGURASI WARNA (PERSIS SAMA DENGAN SIDEBAR) === */
    :root {
        /* Warna Cream diambil dari Sidebar */
        --nav-bg: #F7F3E4;       
        --nav-bg-dark: #eae4d0;   
        
        /* Warna Coklat diambil dari Sidebar */
        --nav-text: #50200C;      
        
        /* Shadow diambil dari Sidebar */
        --nav-shadow: rgba(80, 32, 12, 0.15); 
        --nav-curve: 30px; 
    }

    body {
        background-color: #F7F3E4; 
        padding-top: 150px; 
    }

    /* === NAVBAR STYLE === */
    .modern-navbar {
        position: fixed;
        top: 15px;              
        left: 15px;             
        right: 15px;         
        height: 105px;       
        
        /* Gradient Cream (Sama persis dengan Sidebar) */
        background: linear-gradient(180deg, var(--nav-bg) 0%, var(--nav-bg-dark) 100%);
        
        border-radius: var(--nav-curve); 
        
        /* Shadow & Border (Sama persis dengan Sidebar) */
        box-shadow: 0 20px 40px var(--nav-shadow);
        border: 1px solid #d7ccc8; 
        
        z-index: 1001; 
        padding: 0 40px; 
        transition: all 0.3s ease;

        backdrop-filter: blur(10px); 
        -webkit-backdrop-filter: blur(10px);
    }

    /* === LOGO STYLE === */
    .brand-logo-img {
        max-height: 90px;
        width: 240px;
        display: block;
        background: transparent; 
        /* Drop shadow tipis biar ga flat */
        filter: drop-shadow(0 2px 4px rgba(80, 32, 12, 0.1));
        transition: transform 0.3s ease;
    }
    
    .brand-logo-img:hover {
        transform: scale(1.02); 
    }

    /* === CLOCK WIDGET (CLEAN STYLE) === */
    .clock-widget {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-end;
        color: var(--nav-text); 
    }

    /* Baris Jam */
    .clock-display {
        display: flex;
        align-items: center;
        line-height: 1; 
        margin-bottom: 4px;
    }

    /* Ikon Jam */
    .clock-icon {
        font-size: 1.2rem;
        color: #8D6E63; /* Coklat sedikit muda */
        margin-top: 2px;
    }

    /* Angka Jam Utama */
    #clock-time {
        font-family: 'Inter', sans-serif;
        font-weight: 800;       
        font-size: 2.2rem;      
        letter-spacing: -1px;   
        color: var(--nav-text); /* Coklat Tua Solid */
        
        /* Text shadow dihapus/dibuat sangat tipis agar solid */
        text-shadow: none; 
    }
    
    .clock-zone {
        font-size: 1rem;
        font-weight: 700;
        margin-left: 8px;
        opacity: 0.7;
        align-self: flex-end; 
        padding-bottom: 4px;
        color: var(--nav-text);
    }

    /* Baris Tanggal */
    .date-text {
        font-weight: 700;
        font-size: 1rem; 
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--nav-text);
        opacity: 0.8;
    }

    @media (max-width: 768px) {
        .modern-navbar {
            left: 10px; right: 10px; top: 10px;
            padding: 0 20px;
            height: 90px;
            border-radius: 20px;
        }
        .brand-logo-img { max-height: 50px; }
        
        #clock-time { font-size: 1.5rem; }
        .clock-icon { font-size: 1rem; }
        .date-text { font-size: 0.8rem; }
        
        body { padding-top: 120px; }
    }

    .top-shield {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 25px; /* Sedikit lebih besar dari jarak top navbar (15px) + radius */
        background-color: #F7F3E4; /* WAJIB SAMA dengan warna background Body */
        z-index: 1000; /* Di atas konten, tapi di bawah Navbar */
    }

</style>

<script>
    function updateClock() {
        const now = new Date();
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('clock-date').textContent = now.toLocaleDateString('id-ID', dateOptions);
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0');
        document.getElementById('clock-time').textContent = `${hours}:${minutes}:${seconds}`;
    }
    updateClock(); 
    setInterval(updateClock, 1000);
</script>
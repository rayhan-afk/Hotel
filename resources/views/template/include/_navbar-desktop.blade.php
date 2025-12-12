<nav class="navbar navbar-dark px-3 d-flex justify-content-between" 
     style="background: #C49A6C; position: fixed; top: 0; width: 100%; z-index: 1000;">
    
    <!-- Logo kiri -->
    <a class="navbar-brand" href="{{ route('dashboard.index') }}">
        <img src="{{ asset('img/logo-anda.png') }}" alt="Hotel Logo" class="brand-logo-img-only">
    </a>

    <!-- Jam kanan live -->
    <div class="text-end navbar-clock">
        <div class="time" id="clock-time"></div>
        <div class="date" id="clock-date"></div>
    </div>
</nav>

<style>
    .brand-logo-img-only {
        max-height: 88px;  
        width: auto;       
        display: block;
    }

    body {
        padding-top: 88px; /* geser konten agar tidak tertutup navbar */
    }

    .navbar-clock {
        background: transparent;
        padding: 0;
        margin: 0;
        border: none;
    }

    .navbar-clock .date {
        color: #50200C;
        font-size: 1.25rem;
    }

    .navbar-clock .time {
        color: #50200C;
        font-size: 1.5rem; /* lebih besar */
        font-weight: bold;
        background: transparent;
    }
</style>

<script>
    function updateClock() {
        const now = new Date();

        // 1. FORMAT TANGGAL (Bahasa Indonesia)
        // Menggunakan 'id-ID' akan otomatis mengubah:
        // Wednesday -> Rabu, December -> Desember
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('clock-date').textContent = now.toLocaleDateString('id-ID', dateOptions);

        // 2. FORMAT WAKTU (24 Jam dengan Detik)
        // Kita ambil jam, menit, detik secara manual agar formatnya rapi (HH:MM:SS)
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const seconds = now.getSeconds().toString().padStart(2, '0'); // Tambahan detik biar live

        // Gabungkan string
        // Hasil contoh: 14:30:05 WIB
        document.getElementById('clock-time').textContent = `${hours}:${minutes}:${seconds} WIB`;
    }

    updateClock(); // pertama kali
    setInterval(updateClock, 1000); // update tiap detik
</script>


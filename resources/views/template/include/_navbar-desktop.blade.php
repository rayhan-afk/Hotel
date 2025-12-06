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
        
        // Format waktu hh:mm AM/PM
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        document.getElementById('clock-time').textContent = hours + ':' + minutes + ' ' + ampm;

        // Format tanggal
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('clock-date').textContent = now.toLocaleDateString('en-US', options);
    }

    updateClock(); // pertama kali
    setInterval(updateClock, 1000); // update tiap detik
</script>

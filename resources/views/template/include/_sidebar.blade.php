<div id="sidebar-wrapper" class="lh-sidebar">
    <div class="sidebar-content">
        
        {{-- USER PROFILE (BROWN CARD) --}}
<div class="sidebar-user-container">
    <div class="sidebar-user glass-card">
        <div class="user-avatar-wrapper">
            <img src="{{ auth()->user()->getAvatar() }}" alt="User Avatar" class="user-img">
            <span class="status-dot"></span>
        </div>
        
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role badge-role">{{ auth()->user()->role }}</div>
        </div>

        <div class="dropdown">
            <button class="btn-dots" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            
            {{-- Dropdown Menu (Hanya Keluar) --}}
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-1" style="min-width: 120px;">
                <li>
                    <a class="dropdown-item fw-bold" style="color: #A94442" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i>Keluar
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

        {{-- NAVIGATION MENU --}}
        <nav class="sidebar-nav custom-scrollbar">
            @php
                $role = auth()->user()->role;
                $isDapur = ($role == 'Dapur');
                $isSuper = ($role == 'Super' || $role == 'Superadmin');
                $isManager = ($role == 'Manager');
                $isAdmin = ($role == 'Admin');
                $isHousekeeping = ($role == 'Housekeeping');
                $isKasir = ($role == 'Kasir');
            @endphp

            {{-- 1. GAMBARAN UMUM --}}
            @if(!$isDapur && !$isHousekeeping && !$isKasir)
                <div class="nav-section-label">Main</div>
                <a href="{{ route('dashboard.index') }}"
                   class="nav-item {{ in_array(Route::currentRouteName(), ['dashboard.index', 'chart.dailyGuest']) ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-chart-pie"></i></div>
                    <span class="nav-text">Beranda</span>
                </a>
            @endif

            {{-- 2. PEMESANAN --}}
            @if($isHousekeeping)
                <div class="nav-section-label">Tugas</div>
                <a href="{{ route('room-info.cleaning') }}" class="nav-item {{ request()->routeIs('room-info.cleaning*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-broom"></i></div>
                    <span class="nav-text">Kamar Dibersihkan</span>
                </a>
            @elseif(!$isDapur && !$isKasir)
                <div class="nav-section-label">Front Office</div>
                
                {{-- Info Kamar Dropdown --}}
                <div class="nav-group {{ request()->routeIs(['room-info.*']) ? 'active' : '' }}">
                    <div class="nav-item dropdown-toggle-btn" data-bs-toggle="collapse" data-bs-target="#roomInfoSubmenu">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon"><i class="fas fa-bed"></i></div>
                            <span class="nav-text">Info Kamar</span>
                        </div>
                        <i class="fas fa-chevron-right arrow-icon"></i>
                    </div>
                    <div class="collapse {{ request()->routeIs(['room-info.*']) ? 'show' : '' }}" id="roomInfoSubmenu">
                        <div class="nav-submenu-wrapper">
                            {{-- SUBMENU DENGAN LOGO --}}
                            <a href="{{ route('room-info.available') }}" class="nav-subitem {{ request()->routeIs('room-info.available*') ? 'active' : '' }}">
                                <i class="fas fa-check-circle nav-icon-sub"></i> Tersedia
                            </a>
                            <a href="{{ route('room-info.reservation') }}" class="nav-subitem {{ request()->routeIs('room-info.reservation*') ? 'active' : '' }}">
                                <i class="fas fa-clock nav-icon-sub"></i> Reservasi
                            </a>
                            <a href="{{ route('room-info.cleaning') }}" class="nav-subitem {{ request()->routeIs('room-info.cleaning*') ? 'active' : '' }}">
                                <i class="fas fa-broom nav-icon-sub"></i> Cleaning
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Pemesanan Dropdown --}}
                @if($isSuper || $isManager || $isAdmin)
                    @php $isPemesananActive = request()->routeIs('transaction.checkin.*') || request()->routeIs('fo.cashier.*'); @endphp
                    <div class="nav-group {{ $isPemesananActive ? 'active' : '' }}">
                        <div class="nav-item dropdown-toggle-btn" data-bs-toggle="collapse" data-bs-target="#pemesananSubmenu">
                            <div class="d-flex align-items-center">
                                <div class="nav-icon"><i class="fas fa-concierge-bell"></i></div>
                                <span class="nav-text">Pemesanan</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                        <div class="collapse {{ $isPemesananActive ? 'show' : '' }}" id="pemesananSubmenu">
                            <div class="nav-submenu-wrapper">
                                {{-- SUBMENU DENGAN LOGO --}}
                                <a href="{{ route('transaction.checkin.index') }}" class="nav-subitem {{ request()->routeIs('transaction.checkin.*') ? 'active' : '' }}">
                                    <i class="fas fa-exchange-alt nav-icon-sub"></i> Check In/Out
                                </a>
                                <a href="{{ route('fo.cashier.index') }}" class="nav-subitem {{ request()->routeIs('fo.cashier.*') ? 'active' : '' }}">
                                    <i class="fas fa-cash-register nav-icon-sub"></i> FO Cashier
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 3. OPERATIONS --}}
            @if(!$isDapur && !$isHousekeeping && !$isKasir)
                <div class="nav-section-label">Manajemen</div>

                @if($isManager|| $isSuper)
                    <a href="{{ route('approval.index') }}" class="nav-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-clipboard-check"></i></div>
                        <span class="nav-text">Approval</span>
                    </a>
                @endif

                @if($isSuper || $isManager)
                    <a href="{{ route('user.index') }}" class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-users-cog"></i></div>
                        <span class="nav-text">Staff</span>
                    </a>
                @endif

                <a href="{{ route('customer.index') }}" class="nav-item {{ request()->routeIs('customer.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-user-friends"></i></div>
                    <span class="nav-text">Data Tamu</span>
                </a>

                {{-- Kamar Dropdown --}}
                <div class="nav-group {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'active' : '' }}">
                    <div class="nav-item dropdown-toggle-btn" data-bs-toggle="collapse" data-bs-target="#roomSubmenu">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon"><i class="fas fa-door-open"></i></div>
                            <span class="nav-text">Master Kamar</span>
                        </div>
                        <i class="fas fa-chevron-right arrow-icon"></i>
                    </div>
                    <div class="collapse {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'show' : '' }}" id="roomSubmenu">
                        <div class="nav-submenu-wrapper">
                            {{-- SUBMENU DENGAN LOGO --}}
                            <a href="{{ route('room.index') }}" class="nav-subitem {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit']) ? 'active' : '' }}">
                                <i class="fas fa-list nav-icon-sub"></i> Daftar Kamar
                            </a>
                            <a href="{{ route('type.index') }}" class="nav-subitem {{ in_array(Route::currentRouteName(), ['type.index', 'type.create', 'type.edit']) ? 'active' : '' }}">
                                <i class="fas fa-tag nav-icon-sub"></i> Tipe Kamar
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('ruangrapat.index') }}" class="nav-item {{ request()->routeIs('ruangrapat.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="fas fa-briefcase"></i></div>
                    <span class="nav-text">Ruang Rapat</span>
                </a>
            @endif

            {{-- 4. PERSEDIAAN --}}
            @if($isDapur || $isSuper || $isAdmin || $isManager || $isHousekeeping || $isKasir)
                <div class="nav-section-label">Inventory & POS</div>
                
                @if($isDapur)
                     <a href="{{ route('ingredient.index') }}" class="nav-item {{ request()->routeIs('ingredient.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-carrot"></i></div>
                        <span class="nav-text">Bahan Baku</span>
                    </a>
                     <a href="{{ route('recipes.index') }}" class="nav-item {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-scroll"></i></div>
                        <span class="nav-text">Resep</span>
                    </a>
                @elseif($isHousekeeping)
                    <a href="{{ route('amenity.index') }}" class="nav-item {{ request()->routeIs('amenity.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-soap"></i></div>
                        <span class="nav-text">Amenities</span>
                    </a>
                @elseif($isKasir)
                     <a href="{{ route('pos.index') }}" class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="fas fa-cash-register"></i></div>
                        <span class="nav-text">Kasir (POS)</span>
                    </a>
                @else
                    {{-- Dropdown Inventory --}}
                    <div class="nav-group {{ request()->routeIs(['ingredient.*', 'amenity.*', 'pos.*', 'recipes.*']) ? 'active' : '' }}">
                         <div class="nav-item dropdown-toggle-btn" data-bs-toggle="collapse" data-bs-target="#persediaanSubmenu">
                            <div class="d-flex align-items-center">
                                <div class="nav-icon"><i class="fas fa-boxes"></i></div>
                                <span class="nav-text">Logistik & POS</span>
                            </div>
                            <i class="fas fa-chevron-right arrow-icon"></i>
                        </div>
                         <div class="collapse {{ request()->routeIs(['ingredient.*', 'amenity.*', 'pos.*', 'recipes.*']) ? 'show' : '' }}" id="persediaanSubmenu">
                            <div class="nav-submenu-wrapper">
                                {{-- SUBMENU DENGAN LOGO --}}
                                @if($isSuper) 
                                    <a href="{{ route('ingredient.index') }}" class="nav-subitem {{ request()->routeIs('ingredient.*') ? 'active' : '' }}">
                                        <i class="fas fa-carrot nav-icon-sub"></i> Bahan Baku
                                    </a>
                                @endif
                                <a href="{{ route('pos.index') }}" class="nav-subitem {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                                    <i class="fas fa-cash-register nav-icon-sub"></i> Kasir
                                </a>
                                @if(!$isAdmin)
                                    <a href="{{ route('recipes.index') }}" class="nav-subitem {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                                        <i class="fas fa-scroll nav-icon-sub"></i> Resep
                                    </a>
                                @endif
                                <a href="{{ route('amenity.index') }}" class="nav-subitem {{ request()->routeIs('amenity.*') ? 'active' : '' }}">
                                    <i class="fas fa-soap nav-icon-sub"></i> Amenities
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 5. LAPORAN --}}
            @if(!$isDapur && !$isHousekeeping)
                <div class="nav-section-label">Analisa</div>
                @php $isLaporanActive = in_array(Route::currentRouteName(), ['laporan.kamar.index', 'laporan.rapat.index', 'laporan.pos.index']); @endphp
                
                <div class="nav-group {{ $isLaporanActive ? 'active' : '' }}">
                    <div class="nav-item dropdown-toggle-btn" data-bs-toggle="collapse" data-bs-target="#laporanSubmenu">
                        <div class="d-flex align-items-center">
                            <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                            <span class="nav-text">Laporan</span>
                        </div>
                        <i class="fas fa-chevron-right arrow-icon"></i>
                    </div>
                    <div class="collapse {{ $isLaporanActive ? 'show' : '' }}" id="laporanSubmenu">
                        <div class="nav-submenu-wrapper">
                            {{-- SUBMENU DENGAN LOGO --}}
                            @if(!$isKasir)
                                <a href="{{ route('laporan.kamar.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.kamar.index' ? 'active' : '' }}">
                                    <i class="fas fa-bed nav-icon-sub"></i> Lap. Kamar
                                </a>
                                <a href="{{ route('laporan.rapat.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.rapat.index' ? 'active' : '' }}">
                                    <i class="fas fa-handshake nav-icon-sub"></i> Lap. Rapat
                                </a>
                            @endif
                            @if(!$isAdmin) 
                                <a href="{{ route('laporan.pos.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.pos.index' ? 'active' : '' }}">
                                    <i class="fas fa-cash-register nav-icon-sub"></i> Lap. Kasir
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </nav>

        {{-- BUTTON RESERVASI BARU --}}
        @if(!$isDapur && !$isHousekeeping && !$isKasir)
            <div class="sidebar-action">
                <a href="{{ route('transaction.reservation.createIdentity') }}" class="btn-modern-action">
                    <span class="icon-box"><i class="fas fa-plus"></i></span>
                    <span class="text">Reservasi Baru</span>
                </a>
            </div>
        @endif

    </div>
</div>

<style>
/* === BASE SETUP (WARNA COKLAT SEBELUMNYA) === */
:root {
    --sb-bg: #F7F3E4;        /* Cream Background */
    --sb-bg-dark: #eae4d0;   
    
    /* WARNA FONT KEMBALI KE VERSI SEBELUMNYA */
    --sb-text: #50200C;      /* Coklat Tua Solid (Primary) */
    --sb-text-muted: #50200C; /* Coklat Sedang Solid (Secondary) */
    
    --sb-active-bg: #50200C; /* Coklat Sangat Gelap (Active) */
    --sb-active-text: #ffffff; 
    
    --sb-curve: 30px;
}

.lh-sidebar {
    width: 280px;
    background: linear-gradient(180deg, var(--sb-bg) 0%, var(--sb-bg-dark) 100%);
    position: fixed;
    
    /* === BAGIAN INI YANG DIUBAH BIAR GA NABRAK === */
    /* Rumus: Top Navbar (15px) + Tinggi Navbar (105px) + Celah (20px) = 140px */
    top: 140px; 
    
    left: 20px; 
    bottom: 20px; 
    
    /* Hitung tinggi sisa agar pas layar: 100vh - (Top 140px + Bottom 20px) */
    height: calc(100vh - 160px); 
    
    z-index: 1000;
    border-radius: var(--sb-curve);
    box-shadow: 0 20px 40px rgba(80, 32, 12, 0.15); 
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    border: 1px solid #d7ccc8;
}

.sidebar-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding-bottom: 20px;
}

/* === USER PROFILE (KEMBALI KE GRADIENT SEBELUMNYA) === */
.sidebar-user-container {
    padding: 20px;
}

.glass-card {
    /* Gradient Coklat versi sebelumnya */
    background: linear-gradient(135deg, #50200C 0%, #3E2723 100%);
    box-shadow: 0 8px 20px rgba(80, 32, 12, 0.25);
    border-radius: 20px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.3s ease;
    border: 1px solid #3E2723;
}

.glass-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(80, 32, 12, 0.35);
}

.user-img {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.status-dot {
    width: 10px;
    height: 10px;
    background: #10B981;
    border: 2px solid #50200C;
    border-radius: 50%;
    position: absolute;
    bottom: -2px;
    right: -2px;
}

.user-info { flex: 1; overflow: hidden; }

.user-name {
    color: #ffffff; /* Teks Putih di BG Gelap */
    font-weight: 800;
    font-size: 0.95rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.badge-role {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 0.65rem;
    color: #f1f1f1;
    font-weight: 600;
    margin-top: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-dots {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    padding: 5px;
}
.btn-dots:hover { color: #fff; }

/* === NAVIGATION MENU === */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 0 15px;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d7ccc8; border-radius: 10px; }

.nav-section-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: var(--sb-text-muted); /* Kembali ke warna muted sebelumnya */
    font-weight: 800;
    letter-spacing: 1px;
    margin: 20px 0 10px 10px;
    opacity: 0.8; 
}

/* Menu Item Style */
.nav-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    margin-bottom: 6px;
    border-radius: 16px;
    color: var(--sb-text); /* Kembali ke warna utama sebelumnya */
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    border: 1px solid transparent;
    font-weight: 600;
}

.nav-item:hover {
    background: rgba(80, 32, 12, 0.08); 
    color: #3E2723; 
    transform: translateX(4px);
}

/* Active State */
.nav-item.active, 
.nav-group.active .dropdown-toggle-btn {
    background: var(--sb-active-bg);
    color: var(--sb-active-text);
    box-shadow: 0 6px 15px rgba(80, 32, 12, 0.25);
    font-weight: 700;
}

.nav-item.active .nav-icon, 
.nav-group.active .dropdown-toggle-btn .nav-icon {
    color: var(--sb-active-text);
}

.nav-icon {
    width: 24px;
    font-size: 1.1rem;
    margin-right: 12px;
    display: flex;
    justify-content: center;
    transition: color 0.3s;
    color: var(--sb-text-muted); /* Kembali ke warna muted sebelumnya */
}

.nav-text { flex: 1; font-size: 0.9rem; }

/* Arrow */
.arrow-icon {
    font-size: 0.7rem;
    transition: transform 0.3s ease;
    color: var(--sb-text-muted);
}
.nav-item[aria-expanded="true"] .arrow-icon,
.dropdown-toggle-btn[aria-expanded="true"] .arrow-icon {
    transform: rotate(90deg);
    color: var(--sb-active-text);
}

/* === SUBMENU STYLE (KEMBALI KE VERSI SEBELUMNYA) === */
.nav-submenu-wrapper {
    padding: 5px 0 10px 20px;
    border-left: 2px solid #d7ccc8; 
    margin-left: 20px;
}

.nav-subitem {
    display: flex;
    align-items: center;
    padding: 9px 15px;
    
    /* KEMBALI KE WARNA SEBELUMNYA */
    color: var(--sb-text-muted); /* #795548 */
    
    text-decoration: none;
    font-size: 0.85rem;
    border-radius: 12px;
    margin-bottom: 2px;
    transition: all 0.2s;
    font-weight: 600;
}

.nav-icon-sub {
    width: 18px;
    text-align: center;
    margin-right: 10px;
    font-size: 0.85rem;
    
    /* KEMBALI KE OPACITY SEBELUMNYA */
    opacity: 0.7; 
    color: #4E342E;
}

.nav-subitem:hover {
    color: #3E2723; 
    background: rgba(80, 32, 12, 0.08); 
}
.nav-subitem:hover .nav-icon-sub {
    opacity: 1;
}

.nav-subitem.active {
    background: var(--sb-active-bg); 
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(80, 32, 12, 0.2);
}
.nav-subitem.active .nav-icon-sub {
    color: #ffffff;
    opacity: 1;
}

/* === BTN ACTION === */
.btn-modern-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #5D4037, #3E2723); /* Gradient sebelumnya */
    color: #fff; 
    border-radius: 20px;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(62, 39, 35, 0.25);
    transition: all 0.3s ease;
}
.btn-modern-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(62, 39, 35, 0.35);
    color: #fff;
}

@media (max-width: 991.98px) {
    .lh-sidebar { left: -300px; top: 0; bottom: 0; height: 100vh; width: 280px; border-radius: 0 30px 30px 0; }
    .lh-sidebar.show { left: 0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Handle Dropdown Submenus
    const dropdownBtns = document.querySelectorAll('.dropdown-toggle-btn');
    
    dropdownBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            const targetEl = document.querySelector(targetId);
            
            // Toggle Bootstrap Collapse
            const bsCollapse = bootstrap.Collapse.getOrCreateInstance(targetEl);
            bsCollapse.toggle();

            // Handle Aria & Animation
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
        });

        // Sync arrow state based on collapse events
        const targetId = btn.getAttribute('data-bs-target');
        const targetEl = document.querySelector(targetId);
        
        targetEl.addEventListener('show.bs.collapse', () => btn.setAttribute('aria-expanded', 'true'));
        targetEl.addEventListener('hide.bs.collapse', () => btn.setAttribute('aria-expanded', 'false'));
    });

    // 2. Mobile Sidebar Toggle (Jika Anda punya tombol toggle di header)
    const sidebar = document.querySelector('.lh-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar when clicking outside (Mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        }
    });
});
</script>
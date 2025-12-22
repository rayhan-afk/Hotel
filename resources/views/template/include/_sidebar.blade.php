<div id="sidebar-wrapper" class="lh-sidebar">
    <div class="sidebar-content">
        {{-- ============================================================= --}}
        {{-- USER PROFILE SECTION --}}
        {{-- ============================================================= --}}
        <div class="sidebar-user">
            <div class="user-avatar">
                <img src="{{ auth()->user()->getAvatar() }}" alt="User Avatar" class="rounded-circle">
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role }}</div>
            </div>
            <div class="user-actions">
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
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

        {{-- ============================================================= --}}
        {{-- START NAVIGATION MENU --}}
        {{-- ============================================================= --}}
        <nav class="sidebar-nav">
            
            {{-- DEFINISI VARIABLE ROLE AGAR KODE LEBIH BERSIH --}}
            @php
                $role = auth()->user()->role;
                $isDapur = ($role == 'Dapur');
                $isSuper = ($role == 'Super' || $role == 'Superadmin');
                $isManager = ($role == 'Manager');
                $isAdmin = ($role == 'Admin');
                $isHousekeeping = ($role == 'Housekeeping');
            @endphp

            {{-- 1. GAMBARAN UMUM (DASHBOARD) --}}
            {{-- Logic: Semua KECUALI Dapur & Housekeeping --}}
            @if(!$isDapur && !$isHousekeeping)
                <div class="nav-section">
                    <div class="nav-section-title">Gambaran Umum</div>
                    <a href="{{ route('dashboard.index') }}"
                       class="nav-item {{ in_array(Route::currentRouteName(), ['dashboard.index', 'chart.dailyGuest']) ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Beranda</div>
                            <div class="nav-subtitle">Analisis & Gambaran Umum</div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- 2. PEMESANAN (INFO KAMAR & TRANSAKSI) --}}
            
            {{-- MENU: INFO KAMAR - Khusus untuk Housekeeping (Direct Link) --}}
            @if($isHousekeeping)
                <div class="nav-section-title">Info Kamar</div>
                <a href="{{ route('room-info.cleaning') }}" class="nav-item {{ request()->routeIs('room-info.cleaning*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div class="nav-content">
                        <div class="nav-title">Kamar Dibersihkan</div>
                        <div class="nav-subtitle">Status Pembersihan</div>
                    </div>
                </a>
            @elseif(!$isDapur)
                {{-- MENU: INFO KAMAR - Untuk role lain (Dropdown lengkap) --}}
                <div class="nav-section-title">Pemesanan</div>

                {{-- MENU: INFO KAMAR --}}
                <div class="nav-item dropdown-nav {{ request()->routeIs(['room-info.*']) ? 'active' : '' }} ">
                    <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#roomInfoSubmenu">
                        <div class="nav-icon">
                            <i class="fas fa-bed"></i> 
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Info Kamar</div>
                            <div class="nav-subtitle">Ketersediaan & Status</div>
                        </div>
                        <div class="nav-arrow">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="collapse {{ request()->routeIs(['room-info.*']) ? 'show' : '' }} w-100" id="roomInfoSubmenu">
                        <div class="nav-submenu">
                            <a href="{{ route('room-info.available') }}" class="nav-subitem {{ request()->routeIs('room-info.available*') ? 'active' : '' }} ">
                                <i class="fas fa-check-circle me-2"></i>Kamar Tersedia
                            </a>
                            <a href="{{ route('room-info.reservation') }}" class="nav-subitem {{ request()->routeIs('room-info.reservation*') ? 'active' : '' }} ">
                                <i class="fas fa-clock me-2"></i>Reservasi Kamar
                            </a>
                            <a href="{{ route('room-info.cleaning') }}" class="nav-subitem {{ request()->routeIs('room-info.cleaning*') ? 'active' : '' }}">
                                <i class="fas fa-broom me-2"></i>Kamar Dibersihkan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- MENU: TRANSAKSI (Check-in) --}}
                <a href="{{ route('transaction.checkin.index') }}" 
                class="nav-item {{ request()->routeIs('transaction.checkin.*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="nav-content">
                        <div class="nav-title">Pemesanan</div>
                        <div class="nav-subtitle">Check-in/Check-out Tamu</div>
                    </div>
                </a>
            @endif
            
            {{-- 3. OPERATIONS (Manajemen User, Kamar, Ruang Rapat) --}}
            {{-- Logic: Header Operations tampil untuk Superadmin, Admin, Manager --}}
            @if(!$isDapur && !$isHousekeeping)
                <div class="nav-section">
                    <div class="nav-section-title">Operations</div>
                    
                    {{-- Approval Management (FITUR KHUSUS MANAGER) --}}
                    @if($isManager|| $isSuper)
                        <a href="{{ route('approval.index') }}" class="nav-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Approval Management</div>
                                <div class="nav-subtitle">Edit Kamar & Rapat</div>
                            </div>
                        </a>
                    @endif

                    {{-- User Management --}}
                    {{-- Logic: Superadmin dan Manager (Admin TIDAK BISA) --}}
                    @if($isSuper || $isManager)
                        <a href="{{ route('user.index') }}" class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Manajemen Staff</div>
                                <div class="nav-subtitle">Akses dan Peran</div>
                            </div>
                        </a>
                    @endif

                    {{-- Customer Management --}}
                    <div class="nav-item dropdown-nav {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#customerSubmenu">
                            <div class="nav-icon">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Manajemen Tamu</div>
                                <div class="nav-subtitle">Data Pelanggan</div>
                            </div>
                            <div class="nav-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="collapse {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'show' : '' }} w-100" id="customerSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('customer.index') }}" class="nav-subitem {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'active' : '' }}">
                                    <i class="fas fa-user-friends me-2"></i>Daftar Tamu
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Manajemen Kamar --}}
                    <div class="nav-item dropdown-nav {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#roomSubmenu">
                            <div class="nav-icon">
                                <i class="fas fa-bed"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Manajemen Kamar</div>
                                <div class="nav-subtitle">Kamar, Tipe & Status</div>
                            </div>
                            <div class="nav-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        <div class="collapse {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'show' : '' }} w-100" id="roomSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('room.index') }}" class="nav-subitem {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit']) ? 'active' : '' }}">
                                    <i class="fas fa-door-open me-2"></i>Kamar
                                </a>
                                <a href="{{ route('type.index') }}" class="nav-subitem {{ in_array(Route::currentRouteName(), ['type.index', 'type.create', 'type.edit']) ? 'active' : '' }}">
                                    <i class="fas fa-list me-2"></i>Tipe Kamar
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Ruang Rapat --}}
                    <a href="{{ route('ruangrapat.index') }}" 
                        class="nav-item {{ in_array(Route::currentRouteName(), ['ruangrapat.index', 'ruangrapat.create', 'ruangrapat.edit', 'ruangrapat.show']) ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Ruang Rapat</div>
                            <div class="nav-subtitle">Manajemen Paket</div>
                        </div>
                    </a>
                </div>
            @endif

            {{-- 4. PERSEDIAAN (BAHAN BAKU & AMENITIES) --}}
            {{-- Logic: Tampil untuk SEMUA user, tapi konten beda --}}
            @if($isDapur || $isSuper || $isAdmin || $isManager || $isHousekeeping)
                
                <div class="nav-section-title">{{ ($isDapur || $isHousekeeping) ? 'Persediaan' : 'Persediaan' }}</div>
                
                {{-- A. TAMPILAN KHUSUS DAPUR (Hanya Link Bahan Baku) --}}
                @if($isDapur)
                    <a href="{{ route('ingredient.index') }}" class="nav-item {{ request()->routeIs('ingredient.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-carrot"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Bahan Baku</div>
                            <div class="nav-subtitle">Stok Dapur</div>
                        </div>
                    </a>
                {{-- B. TAMPILAN KHUSUS HOUSEKEEPING (Direct Link) --}}
                @elseif($isHousekeeping)
                    <a href="{{ route('amenity.index') }}" class="nav-item {{ request()->routeIs('amenity.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-soap"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Amenities</div>
                            <div class="nav-subtitle">Stok Kamar</div>
                        </div>
                    </a>
                
                {{-- C. TAMPILAN UNTUK ADMIN/MANAGER/SUPERADMIN (Dropdown) --}}
                @else
                    <div class="nav-item dropdown-nav {{ request()->routeIs(['ingredient.*', 'amenity.*']) ? 'active' : '' }} ">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#persediaanSubmenu">
                            <div class="nav-icon">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Persediaan</div>
                                <div class="nav-subtitle">Bahan Baku & Amenities</div>
                            </div>
                            <div class="nav-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="collapse {{ request()->routeIs(['ingredient.*', 'amenity.*']) ? 'show' : '' }} w-100" id="persediaanSubmenu">
                            <div class="nav-submenu">
                                
                                {{-- Bahan Baku: HANYA Superadmin yang bisa lihat di dalam Dropdown ini --}}
                                {{-- Admin dan Manager TIDAK bisa lihat bahan baku --}}
                                @if($isSuper) 
                                    <a href="{{ route('ingredient.index') }}" class="nav-subitem {{ request()->routeIs('ingredient.*') ? 'active' : '' }} ">
                                        <i class="fas fa-cube me-2"></i>Bahan Baku
                                    </a>
                                @endif
                                
                                <a href="{{ route('pos.index') }}" class="nav-subitem {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                                    <i class="fas fa-cash-register me-2"></i>Kasir
                                </a>
                                
                                <a href="{{ route('recipes.index') }}" class="nav-subitem {{ request()->routeIs('recipes.*') ? 'active' : '' }}">
                                     <i class="fas fa-fw fa-scroll"></i> <span>Atur Resep Menu</span>
                                </a>
                                
                                {{-- Amenities: Semua role di blok else ini (Super, Admin, Manager) bisa lihat --}}
                                <a href="{{ route('amenity.index') }}" class="nav-subitem {{ request()->routeIs('amenity.*') ? 'active' : '' }} ">
                                    <i class="fas fa-soap me-2"></i>Amenities
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif


            {{-- 5. ANALYTICS & ADMINISTRASI --}}
            {{-- Logic: Semua KECUALI Dapur & Housekeeping --}}
            @if(!$isDapur && !$isHousekeeping)
                <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>

                    @php
                        $laporanRoutes = ['laporan.kamar.index', 'laporan.rapat.index'];
                        $isLaporanActive = in_array(Route::currentRouteName(), $laporanRoutes);
                    @endphp
                    
                    <div class="nav-item dropdown-nav {{ $isLaporanActive ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#laporanSubmenu">
                            <div class="nav-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Laporan</div>
                                <div class="nav-subtitle">Keuangan & Analitik</div>
                            </div>
                            <div class="nav-arrow">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="collapse {{ $isLaporanActive ? 'show' : '' }} w-100" id="laporanSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('laporan.kamar.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.kamar.index' ? 'active' : '' }} ">
                                    <i class="fas fa-bed me-2"></i>Laporan Kamar
                                </a>
                                <a href="{{ route('laporan.rapat.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.rapat.index' ? 'active' : '' }} ">
                                    <i class="fas fa-handshake me-2"></i>Laporan Rapat
                                </a>
                                <!-- MENU LAPORAN KASIR (Baru) -->
                                <a href="{{ route('laporan.pos.index') }}" class="nav-subitem {{ Route::currentRouteName() == 'laporan.pos.index' ? 'active' : '' }} ">
                                    <i class="fas fa-cash-register me-2"></i>Laporan Kasir
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </nav>

        {{-- 6. SIDEBAR FOOTER --}}
        {{-- Logic: Semua KECUALI Dapur & Housekeeping --}}
        @if(!$isDapur && !$isHousekeeping)
            <div class="sidebar-footer">
                <a href="{{ route('transaction.reservation.createIdentity') }}" class="btn w-100 quick-action-btn" 
                style="background-color: #8FB8E1; border-color: #8FB8E1; color: #F7F3E4;">
                    <i class="fas fa-plus me-2"></i>
                    Reservasi Baru
                </a>
            </div>
        @endif
        
    </div>

    <button class="sidebar-toggle d-lg-none" id="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

{{-- CSS & JS BAWAAN ANDA (TETAP SAMA) --}}
<style>
.lh-sidebar { width: 280px; background: #50200C; position: fixed; top: 114px; left: 0; height: calc(100vh - 114px); z-index: 1000; overflow: hidden; transition: all 0.3s ease; }
.brand-logo-img-only { max-height: 88px; width: auto; display: block; }
.sidebar-content { display: flex; flex-direction: column; height: 100%; padding: 0; }
.sidebar-brand { display: flex; align-items: center; padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.05); }
.brand-logo { width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-right: 1rem; }
.brand-text h4 { color: white; font-weight: 700; font-size: 1.1rem; }
.brand-text small { color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; }
.sidebar-user { display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.03); }
.user-avatar img { width: 40px; height: 40px; border: 2px solid rgba(255, 255, 255, 0.2); }
.user-info { margin-left: 0.75rem; flex: 1; }
.user-name { color: white; font-weight: 600; font-size: 0.9rem; line-height: 1.2; }
.user-role { color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; }
.user-actions .btn { color: rgba(255, 255, 255, 0.6); border: none; padding: 0.25rem 0.5rem; }
.user-actions .btn:hover { color: white; background: rgba(255, 255, 255, 0.1); }
.sidebar-nav { flex: 1; padding: 0.5rem 0; overflow-y: auto; overflow-x: hidden; }
.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 2px; }
.sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.3); }
.nav-section { margin-bottom: 1.5rem; }
.nav-section-title { color: rgba(255, 255, 255, 0.5); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1.25rem; margin-bottom: 0.5rem; }
.nav-item { display: flex; align-items: center; padding: 0.875rem 1.25rem; color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; position: relative; }
.nav-item:not(.dropdown-nav):hover { color: white; background: rgba(255, 255, 255, 0.08); border-left-color: #3b82f6; }
.nav-item:not(.dropdown-nav).active { color: white; background: rgba(59, 130, 246, 0.15); border-left-color: #3b82f6; }
.nav-icon { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; margin-right: 0.875rem; font-size: 1rem; flex-shrink: 0; }
.nav-content { flex: 1; min-width: 0; }
.nav-title { font-weight: 600; font-size: 0.9rem; line-height: 1.2; margin-bottom: 0.125rem; }
.nav-subtitle { font-size: 0.75rem; color: rgba(255, 255, 255, 0.5); line-height: 1.2; }
.dropdown-nav { display: flex; flex-direction: column; padding: 0; }
.dropdown-nav .nav-toggle { display: flex; align-items: center; padding: 0.875rem 1.25rem; color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: all 0.3s ease; border-left: 3px solid transparent; cursor: pointer; width: 100%; background: none; border: none; text-align: left; }
.dropdown-nav .nav-toggle:hover { color: white; background: rgba(255, 255, 255, 0.08); border-left-color: #3b82f6; }
.dropdown-nav.active .nav-toggle { color: white; background: rgba(59, 130, 246, 0.15); border-left-color: #3b82f6; }
.nav-arrow { width: 20px; display: flex; justify-content: center; transition: transform 0.3s ease; }
.nav-toggle[aria-expanded="true"] .nav-arrow { transform: rotate(180deg); }
.nav-submenu { padding: 0.5rem 0; }
.nav-subitem { display: flex; align-items: center; padding: 0.5rem 1.25rem 0.5rem 3.5rem; color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 0.85rem; transition: all 0.3s ease; }
.nav-subitem:hover { color: white; background: rgba(255, 255, 255, 0.05); }
.nav-subitem.active { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
.sidebar-footer { padding: 1.25rem; border-top: 1px solid rgba(255, 255, 255, 0.1); }
.quick-action-btn { background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; padding: 0.75rem 1rem; transition: all 0.3s ease; }
.quick-action-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
.sidebar-toggle { position: fixed; top: 1rem; left: 1rem; background: #1e293b; color: white; border: none; border-radius: 8px; padding: 0.75rem; font-size: 1.1rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); z-index: 1001; }
.lh-sidebar { width: var(--bs-offcanvas-width); }
@media (min-width: 992px) { .lh-sidebar { width: 280px; } .sidebar-toggle { display: none; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.nav-toggle[data-bs-toggle="collapse"]');
    dropdownToggles.forEach(toggle => {
        const targetId = toggle.getAttribute('data-bs-target');
        const targetElement = document.querySelector(targetId);
        const arrow = toggle.querySelector('.nav-arrow');
        if (targetElement && targetElement.classList.contains('show')) {
            toggle.setAttribute('aria-expanded', 'true');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            toggle.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const collapse = bootstrap.Collapse.getOrCreateInstance(targetElement, { toggle: false });
            collapse.toggle();
        });
        targetElement.addEventListener('shown.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'true');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        });
        targetElement.addEventListener('hidden.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
    });
});
</script>
{{-- ============================================================= --}}
{{-- MOBILE HEADER --}}
{{-- ============================================================= --}}
<header class="mobile-header">
    <div class="container-fluid">
        <!-- Hamburger Menu Button -->
        <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas" aria-controls="mobileOffcanvas">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand -->
        <a href="{{ route('dashboard.index') }}" class="mobile-brand">
            <div class="brand-icon">
                <i class="fas fa-hotel"></i>
            </div>
            <span class="brand-text">Hotel Sawunggaling</span>
        </a>

        <!-- User Profile -->
        <div class="mobile-profile">
            <div class="dropdown">
                <button class="profile-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="profile-info d-none d-sm-block">
                        <div class="profile-name">{{ auth()->user()->name }}</div>
                        <div class="profile-role">{{ auth()->user()->role }}</div>
                    </div>
                    <i class="fas fa-chevron-down profile-arrow d-none d-sm-block"></i>
                </button>

                <!-- User Profile Dropdown Menu -->
                <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 200px;">
                    <li class="dropdown-header">
                        <div class="d-flex align-items-center">
                            <div class="profile-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size: 0.85rem;">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->role }}</div>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2 text-secondary"></i>
                            Pengaturan
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Keluar
                        </a>
                        <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

{{-- ============================================================= --}}
{{-- MOBILE OFFCANVAS SIDEBAR --}}
{{-- ============================================================= --}}
<div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileOffcanvas" aria-labelledby="mobileOffcanvasLabel">
    <button type="button" class="btn-close offcanvas-close-btn" aria-label="Close">
        <i class="fas fa-times"></i>
    </button>

    <div class="sidebar-content">
        {{-- USER PROFILE SECTION --}}
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
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('mobile-sidebar-logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Keluar
                            </a>
                            <form id="mobile-sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- NAVIGATION MENU --}}
        <nav class="sidebar-nav">
            
            {{-- DEFINISI VARIABLE ROLE --}}
            @php
                $role = auth()->user()->role;
                $isDapur = ($role == 'Dapur');
                $isSuper = ($role == 'Super' || $role == 'Superadmin');
                $isManager = ($role == 'Manager');
                $isAdmin = ($role == 'Admin');
            @endphp

            {{-- 1. GAMBARAN UMUM (DASHBOARD) --}}
            @if(!$isDapur)
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

            {{-- 2. PEMESANAN --}}
            @if(!$isDapur)
                <div class="nav-section-title">Pemesanan</div>

                {{-- INFO KAMAR --}}
                <div class="nav-item dropdown-nav {{ request()->routeIs(['room-info.*']) ? 'active' : '' }}">
                    <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#mobileRoomInfoSubmenu">
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
                    <div class="collapse {{ request()->routeIs(['room-info.*']) ? 'show' : '' }} w-100" id="mobileRoomInfoSubmenu">
                        <div class="nav-submenu">
                            <a href="{{ route('room-info.available') }}" 
                               class="nav-subitem {{ request()->routeIs('room-info.available*') ? 'active' : '' }}">
                                <i class="fas fa-check-circle me-2"></i>Kamar Tersedia
                            </a>
                            <a href="{{ route('room-info.reservation') }}" 
                               class="nav-subitem {{ request()->routeIs('room-info.reservation*') ? 'active' : '' }}">
                                <i class="fas fa-clock me-2"></i>Reservasi Kamar
                            </a>
                            <a href="{{ route('room-info.cleaning') }}" 
                               class="nav-subitem {{ request()->routeIs('room-info.cleaning*') ? 'active' : '' }}">
                                <i class="fas fa-broom me-2"></i>Kamar Dibersihkan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- TRANSAKSI (Check-in) --}}
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
            
            {{-- 3. OPERATIONS --}}
            @if(!$isDapur)
                <div class="nav-section">
                    <div class="nav-section-title">Operations</div>
                    
                    {{-- APPROVAL MANAGEMENT (MANAGER) --}}
                    @if($isManager)
                        <a href="{{ route('approval.index') }}" 
                           class="nav-item {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Approval Management</div>
                                <div class="nav-subtitle">Edit Kamar & Rapat</div>
                            </div>
                        </a>
                    @endif

                    {{-- USER MANAGEMENT --}}
                    @if($isSuper || $isManager)
                        <a href="{{ route('user.index') }}" 
                           class="nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <div class="nav-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="nav-content">
                                <div class="nav-title">Manajemen Staff</div>
                                <div class="nav-subtitle">Akses dan Peran</div>
                            </div>
                        </a>
                    @endif

                    {{-- CUSTOMER MANAGEMENT --}}
                    <div class="nav-item dropdown-nav {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#mobileCustomerSubmenu">
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
                        <div class="collapse {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'show' : '' }} w-100" id="mobileCustomerSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('customer.index') }}" 
                                   class="nav-subitem {{ in_array(Route::currentRouteName(), ['customer.index', 'customer.create', 'customer.edit']) ? 'active' : '' }}"                                   >
                                    <i class="fas fa-user-friends me-2"></i>Daftar Tamu
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    {{-- MANAJEMEN KAMAR --}}
                    <div class="nav-item dropdown-nav {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#mobileRoomSubmenu">
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
                        <div class="collapse {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit', 'type.index', 'type.create', 'type.edit', 'roomstatus.index', 'roomstatus.create', 'roomstatus.edit', 'facility.index', 'facility.create', 'facility.edit']) ? 'show' : '' }} w-100" id="mobileRoomSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('room.index') }}" 
                                   class="nav-subitem {{ in_array(Route::currentRouteName(), ['room.index', 'room.show', 'room.create', 'room.edit']) ? 'active' : '' }}">
                                    <i class="fas fa-door-open me-2"></i>Kamar
                                </a>
                                <a href="{{ route('type.index') }}" 
                                   class="nav-subitem {{ in_array(Route::currentRouteName(), ['type.index', 'type.create', 'type.edit']) ? 'active' : '' }}">
                                    <i class="fas fa-list me-2"></i>Tipe Kamar
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- RUANG RAPAT --}}
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

            {{-- 4. PERSEDIAAN --}}
            @if($isDapur || $isSuper || $isAdmin || $isManager)
                <div class="nav-section-title">{{ $isDapur ? 'Dapur' : 'Persediaan' }}</div>
                
                {{-- TAMPILAN KHUSUS DAPUR --}}
                @if($isDapur)
                    <a href="{{ route('ingredient.index') }}" 
                       class="nav-item {{ request()->routeIs('ingredient.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-carrot"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-title">Bahan Baku</div>
                            <div class="nav-subtitle">Stok Dapur</div>
                        </div>
                    </a>
                
                {{-- TAMPILAN ADMIN/MANAGER/SUPERADMIN --}}
                @else
                    <div class="nav-item dropdown-nav {{ request()->routeIs(['ingredient.*', 'amenity.*']) ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#mobilePersediaanSubmenu">
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
                        
                        <div class="collapse {{ request()->routeIs(['ingredient.*', 'amenity.*']) ? 'show' : '' }} w-100" id="mobilePersediaanSubmenu">
                            <div class="nav-submenu">
                                @if($isSuper) 
                                    <a href="{{ route('ingredient.index') }}" 
                                       class="nav-subitem {{ request()->routeIs('ingredient.*') ? 'active' : '' }}">
                                        <i class="fas fa-cube me-2"></i>Bahan Baku
                                    </a>
                                @endif
                                
                                <a href="{{ route('amenity.index') }}" 
                                   class="nav-subitem {{ request()->routeIs('amenity.*') ? 'active' : '' }}">
                                    <i class="fas fa-soap me-2"></i>Amenities
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 5. ANALYTICS --}}
            @if(!$isDapur)
                <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>

                    @php
                        $laporanRoutes = ['laporan.kamar.index', 'laporan.rapat.index'];
                        $isLaporanActive = in_array(Route::currentRouteName(), $laporanRoutes);
                    @endphp
                    
                    <div class="nav-item dropdown-nav {{ $isLaporanActive ? 'active' : '' }}">
                        <div class="nav-toggle" data-bs-toggle="collapse" data-bs-target="#mobileLaporanSubmenu">
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
                        
                        <div class="collapse {{ $isLaporanActive ? 'show' : '' }} w-100" id="mobileLaporanSubmenu">
                            <div class="nav-submenu">
                                <a href="{{ route('laporan.kamar.index') }}" 
                                   class="nav-subitem {{ Route::currentRouteName() == 'laporan.kamar.index' ? 'active' : '' }}">
                                    <i class="fas fa-bed me-2"></i>Laporan Kamar
                                </a>
                                <a href="{{ route('laporan.rapat.index') }}" 
                                   class="nav-subitem {{ Route::currentRouteName() == 'laporan.rapat.index' ? 'active' : '' }}">
                                    <i class="fas fa-handshake me-2"></i>Laporan Rapat
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </nav>

        {{-- SIDEBAR FOOTER --}}
        @if(!$isDapur)
            <div class="sidebar-footer">
                <a href="{{ route('transaction.reservation.createIdentity') }}" 
                   class="btn w-100 quick-action-btn" 
                   style="background-color: #8FB8E1; border-color: #8FB8E1; color: #F7F3E4;">
                    <i class="fas fa-plus me-2"></i>
                    Reservasi Baru
                </a>
            </div>
        @endif
        
    </div>
</div>

{{-- ============================================================= --}}
{{-- STYLES --}}
{{-- ============================================================= --}}
<style>
/* ===== MOBILE HEADER ===== */
.mobile-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 120px;
    background: #50200C;
    z-index: 1050;
    display: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.mobile-header .container-fluid {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 1rem;
}

.hamburger-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    padding: 0.5rem;
    cursor: pointer;
    margin-right: 1rem;
}

.mobile-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
    flex: 1;
}

.mobile-brand .brand-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #8FB8E1, #6A9BC3);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin-right: 0.75rem;
}

.mobile-brand .brand-text {
    color: white;
    font-weight: 700;
    font-size: 1rem;
}

.mobile-profile {
    display: flex;
    align-items: center;
}

.profile-dropdown-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 0.25rem 0.5rem;
    color: white;
    cursor: pointer;
}

.profile-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #8FB8E1, #6A9BC3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.8rem;
}

.profile-info .profile-name {
    font-weight: 600;
    font-size: 0.85rem;
    line-height: 1.2;
}

.profile-info .profile-role {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
}

.profile-arrow {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

/* ===== MOBILE OFFCANVAS ===== */
.mobile-offcanvas {
    width: 280px !important;
    background: #50200C;
    border: none;
}

.offcanvas-close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    cursor: pointer;
    z-index: 10;
    opacity: 1;
}

.offcanvas-close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.mobile-offcanvas .sidebar-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 0;
    padding-top: 3rem;
}

.mobile-offcanvas .sidebar-user {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
}

.mobile-offcanvas .user-avatar img {
    width: 40px;
    height: 40px;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.mobile-offcanvas .user-info {
    margin-left: 0.75rem;
    flex: 1;
}

.mobile-offcanvas .user-name {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.2;
}

.mobile-offcanvas .user-role {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.75rem;
}

.mobile-offcanvas .user-actions .btn {
    color: rgba(255, 255, 255, 0.6);
    border: none;
    padding: 0.25rem 0.5rem;
}

.mobile-offcanvas .user-actions .btn:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

.mobile-offcanvas .sidebar-nav {
    flex: 1;
    padding: 0.5rem 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.mobile-offcanvas .sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.mobile-offcanvas .sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.mobile-offcanvas .sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

.mobile-offcanvas .nav-section {
    margin-bottom: 1.5rem;
}

.mobile-offcanvas .nav-section-title {
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.5rem 1.25rem;
    margin-bottom: 0.5rem;
}

.mobile-offcanvas .nav-item {
    display: flex;
    align-items: center;
    padding: 0.875rem 1.25rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.mobile-offcanvas .nav-item:not(.dropdown-nav):hover {
    color: white;
    background: rgba(255, 255, 255, 0.08);
    border-left-color: #3b82f6;
}

.mobile-offcanvas .nav-item:not(.dropdown-nav).active {
    color: white;
    background: rgba(59, 130, 246, 0.15);
    border-left-color: #3b82f6;
}

.mobile-offcanvas .nav-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.875rem;
    font-size: 1rem;
    flex-shrink: 0;
}

.mobile-offcanvas .nav-content {
    flex: 1;
    min-width: 0;
}

.mobile-offcanvas .nav-title {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 1.2;
    margin-bottom: 0.125rem;
}

.mobile-offcanvas .nav-subtitle {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.5);
    line-height: 1.2;
}

.mobile-offcanvas .dropdown-nav {
    display: flex;
    flex-direction: column;
    padding: 0;
}

.mobile-offcanvas .dropdown-nav .nav-toggle {
    display: flex;
    align-items: center;
    padding: 0.875rem 1.25rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    cursor: pointer;
    width: 100%;
    background: none;
    border: none;
    text-align: left;
}

.mobile-offcanvas .dropdown-nav .nav-toggle:hover {
    color: white;
    background: rgba(255, 255, 255, 0.08);
    border-left-color: #8FB8E1;
}

.mobile-offcanvas .dropdown-nav.active .nav-toggle {
    color: white;
    background: rgba(143, 184, 225, 0.15);
    border-left-color: #8FB8E1;
}

.mobile-offcanvas .nav-arrow {
    width: 20px;
    display: flex;
    justify-content: center;
    transition: transform 0.3s ease;
}

.mobile-offcanvas .nav-toggle[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}

.mobile-offcanvas .nav-submenu {
    padding: 0.5rem 0;
}

.mobile-offcanvas .nav-subitem {
    display: flex;
    align-items: center;
    padding: 0.5rem 1.25rem 0.5rem 3.5rem;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.mobile-offcanvas .nav-subitem:hover {
    color: white;
    background: rgba(255, 255, 255, 0.05);
}

.mobile-offcanvas .nav-subitem.active {
    color: #8FB8E1;
    background: rgba(143, 184, 225, 0.1);
}

.mobile-offcanvas .sidebar-footer {
    padding: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-offcanvas .quick-action-btn {
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.mobile-offcanvas .quick-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(143, 184, 225, 0.4);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 991.98px) {
    .mobile-header {
        display: block;
    }
    
    body {
        padding-top: 60px;
    }
}

@media (min-width: 992px) {
    .mobile-header {
        display: none !important;
    }
    
    .mobile-offcanvas {
        display: none !important;
    }
}
</style>

{{-- ============================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles in mobile sidebar
    const mobileDropdownToggles = document.querySelectorAll('.mobile-offcanvas .nav-toggle[data-bs-toggle="collapse"]');
    
    mobileDropdownToggles.forEach(toggle => {
        const targetId = toggle.getAttribute('data-bs-target');
        const targetElement = document.querySelector(targetId);
        const arrow = toggle.querySelector('.nav-arrow');
        
        // Set initial state
        if (targetElement && targetElement.classList.contains('show')) {
            toggle.setAttribute('aria-expanded', 'true');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        } else {
            toggle.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
        
        // Handle click
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const collapse = bootstrap.Collapse.getOrCreateInstance(targetElement, { toggle: false });
            collapse.toggle();
        });
        
        // Update arrow on show
        targetElement.addEventListener('shown.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'true');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        });
        
        // Update arrow on hide
        targetElement.addEventListener('hidden.bs.collapse', function() {
            toggle.setAttribute('aria-expanded', 'false');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        });
    });
    
    // Auto-close offcanvas when clicking nav links (except dropdown toggles)
    const mobileNavLinks = document.querySelectorAll('.mobile-offcanvas .nav-subitem');
    const mobileOffcanvas = document.getElementById('mobileOffcanvas');
    
    if (mobileOffcanvas) {
        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(mobileOffcanvas);
        
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Small delay to allow navigation to start
                setTimeout(() => {
                    bsOffcanvas.hide();
                }, 100);
            });
        });
    }
});
</script>
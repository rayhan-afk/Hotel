@extends('template.master')
@section('title', 'Dashboard')

@section('content')
{{-- PERBAIKAN 1: Tambah style padding-left: 40px agar berjarak dari sidebar --}}
<div class="dashboard-container fade-in" style="padding-left: 40px; padding-right: 20px;">
    
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <h1 class="fw-bold mb-0" style="color: #50200C; font-size: 2.5rem;">Halo, {{ auth()->user()->name }}!</h1>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge px-4 py-2 rounded-pill" style="background: #EFEBE9; color: #50200C; font-weight: 700; font-size: 1rem;">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        {{-- 1. KAMAR TERSEDIA --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('room-info.available') }}" class="card-stat-link">
                <div class="card card-modern h-100 position-relative">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="stat-icon-wrapper me-3" style="background-color: #A8D5BA;">
                            <i class="fas fa-bed fa-lg" style="color: #50200C;"></i>
                        </div>
                        <div>
                            <div class="stat-number mb-0">{{ $availableRoomsCount }}</div>
                            <div class="stat-label">Kamar Tersedia</div>
                        </div>
                        <span class="badge position-absolute top-0 end-0 m-3 rounded-pill badge-stat" 
                              style="background-color: rgba(168, 213, 186, 0.4); color: #50200C;">
                            Available
                        </span>
                    </div>
                </div>
            </a>
        </div>

        {{-- 2. KAMAR TERPAKAI --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('transaction.checkin.index') }}" class="card-stat-link">
                <div class="card card-modern h-100 position-relative">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="stat-icon-wrapper me-3" style="background-color: #8FB8E1;">
                            <i class="fas fa-check-circle fa-lg" style="color: #50200C;"></i>
                        </div>
                        <div>
                            <div class="stat-number mb-0">{{ $occupiedRoomsCount }}</div>
                            <div class="stat-label">Kamar Terpakai</div>
                        </div>
                        <span class="badge position-absolute top-0 end-0 m-3 rounded-pill badge-stat" 
                              style="background-color: rgba(143, 184, 225, 0.4); color: #50200C;">
                            Occupied
                        </span>
                    </div>
                </div>
            </a>
        </div>

        {{-- 3. RESERVASI --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('room-info.reservation') }}" class="card-stat-link">
                <div class="card card-modern h-100 position-relative">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="stat-icon-wrapper me-3" style="background-color: #C49A6C;">
                            <i class="fas fa-calendar-alt fa-lg" style="color: #50200C;"></i>
                        </div>
                        <div>
                            <div class="stat-number mb-0">{{ $todayReservationsCount }}</div>
                            <div class="stat-label">Reservasi Hari Ini</div>
                        </div>
                        <span class="badge position-absolute top-0 end-0 m-3 rounded-pill badge-stat" 
                              style="background-color: rgba(196, 154, 108, 0.4); color: #50200C;">
                            Reserved
                        </span>
                    </div>
                </div>
            </a>
        </div>

        {{-- 4. CLEANING --}}
        <div class="col-xl-3 col-lg-6 col-md-6">
            <a href="{{ route('room-info.cleaning') }}" class="card-stat-link">
                <div class="card card-modern h-100 position-relative">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="stat-icon-wrapper me-3" style="background-color: #F2C2B8;">
                            <i class="fas fa-broom fa-lg" style="color: #50200C;"></i>
                        </div>
                        <div>
                            <div class="stat-number mb-0">{{ $cleaningRoomsCount }}</div>
                            <div class="stat-label">Sedang Dibersihkan</div>
                        </div>
                        <span class="badge position-absolute top-0 end-0 m-3 rounded-pill badge-stat" 
                              style="background-color: rgba(242, 194, 184, 0.4); color: #50200C;">
                            Cleaning
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card card-modern h-100 border-0 shadow-sm" style="overflow: hidden;">
                
                <div class="card-header border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center" 
                     style="background-color: #F7F3E4;">
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #50200C;">Tamu Hari Ini</h4>
                        <small style="color: #50200C; opacity: 0.8; font-size: 0.9rem;">{{ now()->format('l, F j, Y') }}</small>
                    </div>
                    <button class="btn btn-icon-only" onclick="location.reload()" data-bs-toggle="tooltip" title="Refresh">
                        <i class="fas fa-sync-alt fa-lg" style="color: #50200C;"></i>
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 1rem;">
                            
                            <thead style="background-color: #F7F3E4; border-bottom: 1px solid rgba(80, 32, 12, 0.1);">
                                <tr>
                                    <th class="ps-4 py-3" style="color: #50200C; background-color: #F7F3E4;">Tamu</th>
                                    <th style="color: #50200C; background-color: #F7F3E4;">Kamar</th>
                                    <th style="color: #50200C; background-color: #F7F3E4;">Check In/Out</th>
                                    <th class="text-center" style="color: #50200C; background-color: #F7F3E4;">Sarapan</th>
                                    <th style="color: #50200C; background-color: #F7F3E4;">Total</th>
                                    <th class="text-end pe-4" style="color: #50200C; background-color: #F7F3E4;">Status</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $transaction->customer->user->getAvatar() }}"
                                                     class="avatar-circle me-3" alt="Avatar">
                                                <div>
                                                    <a href="{{ route('customer.show', ['customer' => $transaction->customer->id]) }}" 
                                                       class="fw-bold text-decoration-none" style="color: #50200C; font-size: 1.05rem;">
                                                        {{ $transaction->customer->name }}
                                                    </a>
                                                    <div class="mt-1">
                                                        <span class="badge border" style="background-color: #fff; color: #50200C; font-size: 0.75rem;">
                                                            {{ $transaction->customer->customer_group ?? 'WalkIn' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('room.show', ['room' => $transaction->room->id]) }}" class="fw-bold text-decoration-none" style="color: #50200C; font-size: 1.05rem;">
                                                Room {{ $transaction->room->number }}
                                            </a>
                                            <div class="small" style="color: #50200C; opacity: 0.7;">{{ $transaction->room->type->name ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div style="color: #50200C;">In: {{ Helper::dateFormat($transaction->check_in) }}</div>
                                            <div style="color: #50200C;">Out: {{ Helper::dateFormat($transaction->check_out) }}</div>
                                        </td>
                                        <td class="text-center">
                                            @if($transaction->breakfast == 'Yes')
                                                <span class="badge" style="background-color: #A8D5BA; color: #50200C; border-radius: 10px; font-size: 0.85rem;">Ya</span>
                                            @else
                                                <span class="badge" style="background-color: #F2C2B8; color: #50200C; border-radius: 10px; font-size: 0.85rem;">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold" style="color: #50200C; font-size: 1.05rem;">
                                            {{ Helper::convertToRupiah($transaction->total_price) }}
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($transaction->status == 'Check In')
                                                <span class="badge px-3 py-2" style="background-color: #8FB8E1; color: #50200C; border-radius: 15px; font-size: 0.85rem;">Check In</span>
                                            @elseif($transaction->status == 'Reservation')
                                                <span class="badge px-3 py-2" style="background-color: #FAE8A4; color: #50200C; border-radius: 15px; font-size: 0.85rem;">Reserved</span>
                                            @else
                                                <span class="badge bg-light text-dark px-3 py-2 rounded-pill" style="font-size: 0.85rem;">{{ $transaction->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                                <i class="fas fa-inbox fa-3x mb-3" style="color: #50200C;"></i>
                                                <p class="mb-0" style="color: #50200C; font-size: 1.1rem;">Belum ada tamu check-in hari ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-modern h-100 border-0 shadow-sm" style="overflow: hidden;">
                <div class="card-header border-0 pt-4 px-4 pb-0" style="background-color: #F7F3E4;">
                    <h4 class="fw-bold mb-1" style="color: #50200C;">Statistik Tamu</h4>
                    <small style="color: #50200C; opacity: 0.8; font-size: 0.9rem;">Bulan {{ Helper::thisMonth() }} {{ Helper::thisYear() }}</small>
                </div>
                
                <div class="card-body d-flex flex-column justify-content-center align-items-center pt-2">
                    <div class="text-center mb-4 mt-3">
                        <h2 class="fw-bold mb-0" style="color: #50200C; font-size: 3.5rem;">{{ $thisMonth }}</h2>
                        <span class="text-uppercase small ls-1" style="color: #50200C; font-size: 1rem;">Total Tamu</span>
                    </div>

                    <div class="chart-container w-100 position-relative mb-3" style="height: 220px;">
                        <canvas id="visitors-chart" 
                                this-year="{{ Helper::thisYear() }}" 
                                this-month="{{ Helper::thisMonth() }}">
                        </canvas>
                    </div>

                    <div class="d-flex gap-4 mt-auto">
                        <div class="d-flex align-items-center">
                            <span class="dot-indicator me-2" style="background-color: #50200C;"></span>
                            <small class="fw-semibold" style="color: #50200C; font-size: 0.9rem;">Bulan Ini</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="dot-indicator me-2" style="background-color: #C49A6C;"></span>
                            <small style="color: #50200C; opacity: 0.7; font-size: 0.9rem;">Bulan Lalu</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h4 class="fw-bold mb-4 ps-2" style="color: #50200C;">Akses Cepat</h4>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('transaction.reservation.createIdentity') }}" class="btn-action-card" style="background-color: #A8D5BA;">
                <div class="icon-box bg-white">
                    <i class="fas fa-plus" style="color: #50200C;"></i>
                </div>
                <div class="action-text">
                    <span class="title">Reservasi Baru</span>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('customer.index') }}" class="btn-action-card" style="background-color: #8FB8E1;">
                <div class="icon-box bg-white">
                    <i class="fas fa-users" style="color: #50200C;"></i>
                </div>
                <div class="action-text">
                    <span class="title">Data Tamu</span>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('room.index') }}" class="btn-action-card" style="background-color: #C49A6C;">
                <div class="icon-box bg-white">
                    <i class="fas fa-bed" style="color: #50200C;"></i>
                </div>
                <div class="action-text">
                    <span class="title">Master Kamar</span>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <a href="{{ route('laporan.kamar.index') }}" class="btn-action-card" style="background-color: #F2C2B8;">
                <div class="icon-box bg-white">
                    <i class="fas fa-file-invoice" style="color: #50200C;"></i>
                </div>
                <div class="action-text">
                    <span class="title">Laporan</span>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </a>
        </div>
    </div>

</div>

<style>
/* === CUSTOM STYLE FOR DASHBOARD === */

/* Card Modern Style */
.card-modern {
    background: #FFFFFF;
    border: 1px solid rgba(80, 32, 12, 0.05);
    border-radius: 20px; 
    box-shadow: 0 10px 30px rgba(80, 32, 12, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.card-stat-link { text-decoration: none; display: block; height: 100%; }
.card-stat-link:hover .card-modern {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(80, 32, 12, 0.1);
}

/* Stat Icons */
.stat-icon-wrapper {
    width: 60px; /* Ukuran diperbesar */
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.stat-number {
    font-family: 'Inter', sans-serif;
    font-size: 2.5rem; /* Font diperbesar */
    font-weight: 800;
    color: #50200C; 
    line-height: 1;
}

.stat-label {
    color: #50200C;
    opacity: 0.8;
    font-size: 1.1rem; /* Font diperbesar */
    font-weight: 600;
    margin-top: 2px;
}

/* Badge Stat di pojok */
.badge-stat {
    font-size: 0.85rem !important; /* Font diperbesar */
}

/* Avatar */
.avatar-circle {
    width: 50px; /* Diperbesar dikit */
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #F7F3E4;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Action Cards */
.btn-action-card {
    display: flex;
    align-items: center;
    padding: 20px;
    border-radius: 20px; 
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(80, 32, 12, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.2);
}

.btn-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(80, 32, 12, 0.2);
    filter: brightness(105%);
}

.btn-action-card .icon-box {
    width: 60px; /* Diperbesar */
    height: 60px;
    border-radius: 50%; 
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem; /* Icon diperbesar */
    margin-right: 15px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.btn-action-card .action-text {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.btn-action-card .title {
    color: #50200C; 
    font-weight: 800;
    font-size: 1.2rem; /* Font diperbesar */
}

.btn-action-card .arrow {
    color: #50200C;
    opacity: 0.5;
    transition: transform 0.3s;
    font-size: 1.2rem;
}

.btn-action-card:hover .arrow {
    transform: translateX(3px);
    opacity: 1;
}

/* Chart Indicators */
.dot-indicator { width: 12px; height: 12px; border-radius: 50%; } /* Diperbesar */

/* Utilities */
.btn-icon-only {
    border: none;
    background: transparent;
    color: #50200C;
    transition: transform 0.2s;
}
.btn-icon-only:hover { transform: rotate(90deg); }

.ls-1 { letter-spacing: 1px; }

/* Fade In Animation */
.fade-in { animation: fadeIn 0.6s ease-in-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection
@extends('template.master')
@section('title', 'FO Cashier - Daftar Tamu')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- HEADER INFORMASI (STYLE MIRIP SHOW) --}}
            <div class="card mb-4 shadow-sm border-0 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color: #50200C;">
                                <i class="fas fa-cash-register me-2"></i>Front Office Cashier
                            </h3>
                            <p class="text-muted mb-0">Kelola tagihan tamu yang sedang menginap (In-House).</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end d-none d-md-block">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">Total In-House</small>
                                <span class="fw-bold fs-3 text-success">{{ $transactions->total() }}</span> <span class="text-muted small">Tamu</span>
                            </div>
                            <div class="vr h-100 mx-2 text-muted"></div>
                            <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary px-3 shadow-sm">
                                <i class="fas fa-arrow-left me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEARCH BAR --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: linear-gradient(to right, #fffcf5, #ffffff);">
                    <form action="{{ route('fo.cashier.index') }}" method="GET">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0 ps-4">
                                <i class="fas fa-search text-muted opacity-50"></i>
                            </span>
                            <input type="text" name="keyword" class="form-control border-0 bg-white" 
                                   placeholder="Cari Nomor Kamar atau Nama Tamu..." 
                                   value="{{ request('keyword') }}"
                                   style="font-size: 0.95rem;">
                            
                            <button class="btn text-white fw-bold px-5" type="submit" 
                                    style="background: linear-gradient(to right, #50200C, #8B4513);">
                                CARI
                            </button>
                            
                            @if(request('keyword'))
                                <a href="{{ route('fo.cashier.index') }}" class="btn btn-light border-start" title="Reset Pencarian">
                                    <i class="fas fa-times text-danger"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- LIST DAFTAR TAMU --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #f0f0f0; color: #50200C;">
                    <span>
                        <i class="fas fa-list-ul me-2"></i> 
                        {{ request('keyword') ? 'Hasil Pencarian' : 'Daftar Tamu Menginap' }}
                    </span>
                    @if(request('keyword'))
                        <small class="text-muted fw-normal">Ditemukan {{ $transactions->total() }} data</small>
                    @endif
                </div>
                
                <div class="list-group list-group-flush">
                    @forelse($transactions as $trx)
                        <div class="list-group-item list-group-item-action p-4 border-bottom hover-card transition-all">
                            <div class="row align-items-center">
                                
                                {{-- NOMOR KAMAR (BULAT BESAR) --}}
                                <div class="col-auto">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white fw-bold" 
                                         style="width: 60px; height: 60px; font-size: 1.2rem; background: linear-gradient(45deg, #50200C, #7a2e15);">
                                        {{ $trx->room->number }}
                                    </div>
                                </div>

                                {{-- INFO TAMU --}}
                                <div class="col ms-2">
                                    {{-- Nama Tamu (Sekarang Bisa Diklik) --}}
                                    <h5 class="mb-1 fw-bold">
                                        <a href="{{ route('customer.show', $trx->customer->id) }}" 
                                           class="text-decoration-none text-dark hover-underline">
                                            {{ $trx->customer->name }}
                                        </a>
                                    </h5>

                                    <div class="text-muted small mb-1">
                                        <i class="fas fa-bed me-1 text-warning"></i> {{ $trx->room->type->name }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar-check me-1 text-primary"></i> Check-in: {{ \Carbon\Carbon::parse($trx->check_in)->format('d M Y, H:i') }}
                                    </div>
                                    <span class="badge bg-light text-dark border rounded-pill px-3">
                                        <i class="fas fa-clock me-1 text-secondary"></i> {{ $trx->getDateDifferenceWithPlural() }}
                                    </span>
                                </div>

                                {{-- TOMBOL AKSI --}}
                                <div class="col-auto text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        
                                        {{-- [BARU] TOMBOL PROFIL --}}
                                        <a href="{{ route('customer.show', $trx->customer->id) }}" 
                                           class="btn btn-light border rounded-pill px-3 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                           style="color: #50200C; width: 45px; height: 45px;"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Lihat Profil Tamu">
                                            <i class="fas fa-id-card fa-lg"></i>
                                        </a>

                                        {{-- TOMBOL BUKA FOLIO --}}
                                        <a href="{{ route('fo.cashier.show', $trx->id) }}" 
                                           class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm fw-bold d-flex align-items-center"
                                           style="border-color: #50200C; color: #50200C;">
                                            Buka Folio <i class="fas fa-arrow-right ms-2"></i>
                                        </a>

                                    </div>
                                </div>

                            </div>
                        </div>
                    @empty
                        {{-- EMPTY STATE (SAMA SEPERTI SEBELUMNYA) --}}
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <span class="fa-stack fa-2x opacity-25">
                                    <i class="fas fa-circle fa-stack-2x text-secondary"></i>
                                    <i class="fas fa-search fa-stack-1x fa-inverse"></i>
                                </span>
                            </div>
                            <h5 class="text-muted fw-bold">Tidak ada tamu ditemukan</h5>
                            <p class="small text-muted mb-0">
                                @if(request('keyword'))
                                    Coba gunakan kata kunci pencarian lain.
                                @else
                                    Saat ini belum ada tamu yang statusnya Check-In.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- PAGINATION --}}
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Custom Hover Effect */
    .hover-card {
        border-left: 4px solid transparent;
        transition: all 0.2s ease-in-out;
    }
    .hover-card:hover {
        background-color: #fffcf5; /* Warna krem sangat muda */
        border-left: 4px solid #50200C; /* Aksen Maroon di kiri saat hover */
        padding-left: 1.8rem !important; /* Efek geser sedikit */
    }
    
    /* Styling Tombol Buka Folio saat Hover */
    .btn-outline-dark:hover {
        background-color: #50200C !important;
        border-color: #50200C !important;
        color: white !important;
    }

    /* Efek hover untuk nama tamu */
    .hover-underline:hover {
        text-decoration: underline !important;
        color: #50200C !important;
    }
</style>
@endsection
@extends('template.master')
@section('title', 'FO Cashier - Daftar Tamu')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- HEADER INFORMASI --}}
            <div class="card mb-4 shadow-sm border-0" style="background-color: #F7F3E4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color: #50200C;">
                                <i class="fas fa-cash-register me-2"></i>Front Office Cashier
                            </h3>
                            <p class="mb-0" style="color: #50200C">Kelola tagihan tamu yang sedang menginap (In-House).</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end d-none d-md-block">
                                <small class="d-block text-uppercase" style="color: #50200C; font-size: 0.75rem; letter-spacing: 1px;">Total In-House</small>
                                <span class="fw-bold fs-3 text-success">{{ $transactions->total() }}</span> <span class="small" style="color: #50200C">Tamu</span>
                            </div>
                            <div class="vr h-100 mx-2" style="color: #50200C"></div>
                            <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary px-3 shadow-sm">
                                <i class="fas fa-arrow-left me-1"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEARCH BAR --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4" style="background: #F7F3E4">
                    <form action="{{ route('fo.cashier.index') }}" method="GET">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-0 ps-4">
                                <i class="fas fa-search" style="color: #50200C"></i>
                            </span>
                            <input type="text" name="keyword" class="form-control border-0 bg-white" 
                                   placeholder="Cari Nomor Kamar atau Nama Tamu..." 
                                   value="{{ request('keyword') }}"
                                   style="color: #50200C; font-size: 0.95rem;">
                            
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
                        <small class="fw-normal" style="color: #50200C">Ditemukan {{ $transactions->total() }} data</small>
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
                                    <h5 class="mb-1 fw-bold">
                                        <a href="{{ route('customer.show', $trx->customer->id) }}" 
                                           class="text-decoration-none hover-underline" style="color: #50200C">
                                            {{ $trx->customer->name }}
                                        </a>
                                    </h5>

                                    <div class="small mb-2" style="color: #50200C">
                                        <i class="fas fa-bed me-1" style="color: #FAE8A4"></i> {{ $trx->room->type->name }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar-check me-1" style="color: #8FB8E1"></i> Check-in: {{ \Carbon\Carbon::parse($trx->check_in)->format('d M Y, H:i') }}
                                    </div>

                                    {{-- Info Durasi & Sisa Tagihan --}}
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light border rounded-pill px-3" style="color: #50200C">
                                            <i class="fas fa-clock me-1" style="color: #50200C"></i> {{ $trx->getDateDifferenceWithPlural() }}
                                        </span>

                                        @php
                                            $sisaBayar = $trx->total_price - $trx->paid_amount;
                                        @endphp

                                        @if($sisaBayar > 0)
                                            {{-- [MODERN] BUTTON BAYAR LUNAS (MIRIP CHECKIN) --}}
                                            <button type="button" 
                                                    class="btn btn-sm d-flex align-items-center justify-content-between p-1 pe-3 shadow-sm btn-quick-pay"
                                                    style="background-color: #fff5f5; border: 1px solid #ffc9c9; border-radius: 50px; min-width: 145px; transition: all 0.2s;"
                                                    onmouseover="this.style.backgroundColor='#ffe0e0'; this.style.borderColor='#A94442';"
                                                    onmouseout="this.style.backgroundColor='#fff5f5'; this.style.borderColor='#ffc9c9';"
                                                    onclick="quickPay('{{ $trx->id }}', '{{ addslashes($trx->customer->name) }}', '{{ number_format($sisaBayar, 0, ',', '.') }}')"
                                                    title="Klik untuk Melunasi Tagihan">
                                                
                                                <span class="badge rounded-pill badge-red me-2" style="color: #F7F3E4; font-size: 10px; padding: 5px 10px;">
                                                    BAYAR <i class="fas fa-chevron-right ms-1"></i>
                                                </span>
                                                
                                                <span class="fw-bold" style="color: #A94442; font-size: 12px; font-weight: 800;">
                                                    Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                                                </span>
                                            </button>
                                        @else
                                            <span class="badge rounded-pill d-inline-flex align-items-center" 
                                                  style="background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; padding: 6px 12px;">
                                                <i class="fas fa-check-circle me-1"></i> Lunas
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI --}}
                                <div class="col-auto text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        
                                        <a href="{{ route('customer.show', $trx->customer->id) }}" 
                                           class="btn btn-light border rounded-pill px-3 py-2 shadow-sm d-flex align-items-center justify-content-center"
                                           style="color: #50200C; width: 45px; height: 45px;"
                                           data-bs-toggle="tooltip" 
                                           title="Lihat Profil Tamu">
                                            <i class="fas fa-id-card fa-lg"></i>
                                        </a>

                                        <a href="{{ route('fo.cashier.show', $trx->id) }}" 
                                           class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm fw-bold d-flex align-items-center"
                                           style="border-color: #50200C; color: #50200C;">
                                            Buka Tagihan <i class="fas fa-arrow-right ms-2"></i>
                                        </a>

                                    </div>
                                </div>

                            </div>
                        </div>
                    @empty
                        {{-- EMPTY STATE --}}
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

{{-- SCRIPT QUICK PAY --}}
<script>
    function quickPay(id, name, amount) {
        Swal.fire({
            title: 'Pelunasan Tagihan',
            html: `
                    <div class="text-center mb-3">
                        <div class="mb-2" style="color: #50200C">Total Kekurangan Pembayaran</div>
                        <h2 class="fw-bold" style="color: #A94442">${amount}</h2>
                        <div class="badge bg-light mt-2 border" style="color: #50200C">Tamu: ${name}</div>
                    </div>
                    <p class="small" style="color: #50200C">Klik tombol di bawah untuk mencatat pelunasan tunai.</p>
                `,
                icon: 'warning',
                background: '#F7F3E4',
                showCancelButton: true,
                confirmButtonColor: '#A8D5BA',
                cancelButtonColor: '#F2C2B8',
                confirmButtonText: '<i class="fas fa-check-circle me-1"></i> Lunasi Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: "text-50200C",
                    cancelButton: "text-50200C",
                    title: "text-50200C",
                    htmlContainer: "text-50200C"
                }
            }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Loading
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });

                // Kirim Request
                $.ajax({
                    url: `/transaction/pay-remaining/${id}`,
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Lunas!',
                            text: 'Tagihan berhasil dilunasi.',
                            confirmButtonColor: '#50200C'
                        }).then(() => {
                            location.reload(); 
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal', xhr.responseJSON ? xhr.responseJSON.message : 'Error', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
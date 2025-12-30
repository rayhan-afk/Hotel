@extends('template.master')
@section('title', 'Point of Sales')

@section('content')
<style>
    /* CSS Styles */
    .product-card {
        border: 1px solid #e0e0e0; border-radius: 12px; background: #fff;
        transition: all 0.2s; cursor: pointer; height: 100%; overflow: hidden;
        user-select: none; position: relative;
    }
    .product-card:active { transform: scale(0.98); }
    .product-card:hover {
        transform: translateY(-3px); box-shadow: 0 5px 15px rgba(80, 32, 12, 0.1); border-color: #50200C;
    }
    .product-img-box {
        height: 140px; background-color: #F7F3E4; display: flex;
        align-items: center; justify-content: center; color: #50200C;
    }
    .cart-container {
        background-color: #FDFBF7; border-left: 1px solid #e0e0e0;
        height: 100%; min-height: 500px; display: flex; flex-direction: column;
    }
    .cart-items-scroll::-webkit-scrollbar { width: 6px; }
    .cart-items-scroll::-webkit-scrollbar-thumb { background-color: #D7CCC8; border-radius: 10px; }
    @keyframes popIn { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    .cart-item-anim { animation: popIn 0.3s ease-out; }
</style>

<div class="container-fluid">
    {{-- Header Bar --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-5 col-12 mb-3 mb-md-0">
            <a href="{{ route('pos.history') }}" class="add-room-btn text-decoration-none text-center d-inline-block">
                <i class="fas fa-history me-2"></i>Riwayat Transaksi
            </a>
        </div>
        <div class="col-md-7 col-12 d-flex justify-content-md-end justify-content-start">
            <div class="stock-status-compact d-flex align-items-center flex-wrap" 
                 style="background-color: #F7F3E4; border: 1px solid #e0e0e0; padding: 10px 20px; border-radius: 12px; gap: 15px;">
                <div style="font-size: 14px; color: #50200C; font-weight: bold;">
                    <i class="fas fa-clock me-2"></i>Info Shift:
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; align-items: center;">
                    <span class="badge" style="background-color: #D7CCC8; color: #50200C !important;">
                        <i class="fas fa-user me-1"></i>KASIR
                    </span>
                    <span style="color: #50200C; font-weight: 600;">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span style="color: #ccc;">|</span>
                    <span class="badge" style="background-color: #FAE8A4; color: #50200C !important;">
                        <i class="fas fa-calendar-alt me-1"></i>TANGGAL
                    </span>
                    <span style="color: #50200C; font-weight: 600;">{{ date('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="professional-table-container" style="padding: 0; overflow: hidden; height: calc(100vh - 100px);">
    <div class="row g-0 h-100">
        
        {{-- ========================================== --}}
        {{-- KIRI: Menu List & Filter (col-md-8) --}}
        {{-- ========================================== --}}
        <div class="col-md-8 p-4 h-100 overflow-auto">
            
            {{-- HEADER: Judul & Search Bar --}}
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="mb-1" style="color: #50200C;"><i class="fas fa-cash-register me-2"></i>Menu Restoran</h4>
                    <p class="mb-0 text-muted small">Pilih kategori atau cari menu</p>
                </div>
                
                {{-- SEARCH INPUT (PENTING: ID="searchInput") --}}
                <div class="input-group shadow-sm" style="width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0 bg-white" placeholder="Cari menu...">
                </div>
            </div>

            {{-- KATEGORI FILTER --}}
            <div class="d-flex align-items-center gap-2 overflow-auto pb-3 mb-2" style="white-space: nowrap;">
                
                {{-- Hidden Input (PENTING: ID="categoryFilter") --}}
                <input type="hidden" id="categoryFilter" value="all">

                <button type="button" class="btn btn-primary px-4 rounded-pill cat-btn category-filter-btn active shadow-sm" data-category="all">
                    <i class="fas fa-th-large me-1"></i> Semua
                </button>

                <button type="button" class="btn btn-outline-primary px-4 rounded-pill cat-btn category-filter-btn shadow-sm" data-category="Food">
                    <i class="fas fa-utensils me-1"></i> Makanan
                </button>

                <button type="button" class="btn btn-outline-primary px-4 rounded-pill cat-btn category-filter-btn shadow-sm" data-category="Beverage">
                    <i class="fas fa-glass-martini-alt me-1"></i> Minuman
                </button>

                <button type="button" class="btn btn-outline-primary px-4 rounded-pill cat-btn category-filter-btn shadow-sm" data-category="Snack">
                    <i class="fas fa-cookie-bite me-1"></i> Camilan
                </button>

                {{-- Looping Kategori Tambahan --}}
                @foreach($categories as $cat)
                    @php $catName = is_object($cat) ? $cat->category : $cat; @endphp
                    @if(!in_array($catName, ['Food', 'Beverage', 'Snack']))
                        <button type="button" class="btn btn-outline-primary px-4 rounded-pill cat-btn category-filter-btn shadow-sm" data-category="{{ $catName }}">
                            {{ $catName }}
                        </button>
                    @endif
                @endforeach
            </div>


                {{-- Grid Menu --}}
                <div class="row g-3" id="menuContainer" style="max-height: 600px; overflow-y: auto; padding-right: 5px;">
                    @forelse($menus as $menu)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 menu-item" 
                         data-name="{{ strtolower($menu->name) }}" 
                         data-category="{{ $menu->category }}">
                        <div class="product-card" 
                             data-menu-id="{{ $menu->id }}"
                             data-menu-name="{{ addslashes($menu->name) }}"
                             data-menu-price="{{ $menu->price }}">
                            <div class="product-img-box">
                                @if($menu->image)
                                    <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" style="max-height: 100%; max-width: 100%;">
                                @else
                                    <i class="fas fa-utensils fa-3x" style="opacity: 0.5;"></i>
                                @endif
                            </div>
                            <div class="p-3">
                                <h6 class="fw-bold mb-1 text-truncate" style="color: #50200C;">{{ $menu->name }}</h6>
                                <small class="text-muted d-block mb-2">
                                    Stok: <span class="badge {{ $menu->stock > 5 ? 'bg-success' : 'bg-danger' }}">{{ $menu->stock }}</span>
                                </small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold" style="color: #50200C;">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 30px; height: 30px; padding: 0; color: #50200C; border-color: #50200C;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5"><h5 class="text-muted">Belum ada data menu.</h5></div>
                    @endforelse
                </div>
            </div>

            {{-- KANAN: Keranjang --}}
            <div class="col-md-4">
                <div class="cart-container">
                    <div class="p-4 border-bottom" style="background: #F7F3E4;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold" style="color: #50200C;"><i class="fas fa-shopping-basket me-2"></i>Pesanan</h5>
                            <button onclick="clearCart()" class="btn btn-sm text-danger fw-bold" style="font-size: 0.8rem;">Reset</button>
                        </div>
                    </div>

                    <div id="cartItems" class="cart-items-scroll flex-grow-1 p-3 overflow-auto">
                        <div class="text-center text-muted mt-5">
                            <i class="fas fa-shopping-cart fa-2x mb-2" style="opacity: 0.3"></i>
                            <p>Keranjang kosong</p>
                        </div>
                    </div>

                    <div class="p-4 border-top bg-white">
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>Subtotal</span><span id="subtotalDisplay">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted small">
                            <span>Pajak (0%)</span><span id="taxDisplay">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="h5 fw-bold" style="color: #50200C;">Total</span>
                            <span class="h4 fw-bold" style="color: #50200C;" id="totalDisplay">Rp 0</span>
                        </div>
                        <button class="add-room-btn w-100 justify-content-center py-3 fs-6" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fas fa-money-bill-wave me-2"></i> Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PEMBAYARAN --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color: #50200C;">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <small class="text-muted">Total Tagihan</small>
                    <h3 class="fw-bold mb-0" style="color: #50200C;" id="modalTotalDisplay">Rp 0</h3>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Uang Diterima</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="payAmount" class="form-control" placeholder="Masukkan nominal..." oninput="calculateChange()">
                    </div>
                </div>
                <div class="d-flex justify-content-between alert alert-light border">
                    <span class="fw-bold">Kembalian:</span>
                    <span class="fw-bold text-success" id="changeDisplay">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn text-white" style="background-color: #50200C;" onclick="processPayment()">Proses Bayar</button>
            </div>
        </div>
    </div>
</div>

{{-- CONFIG FOR EXTERNAL JS --}}
<script>
    window.posConfig = {
        storeRoute: "{{ route('pos.store') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>


@endsection
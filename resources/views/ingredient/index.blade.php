@extends('template.master')
@section('title', 'Persediaan Bahan Baku')

@section('content')


<div class="container-fluid">
    
   {{-- BARIS ATAS: Tombol Tambah (Kiri) & Status Stok (Kanan) --}}
<div class="row mb-4 align-items-center">
    
    {{-- 1. BAGIAN TOMBOL (Ubah jadi col-md-7, tambah d-flex dan gap-2) --}}
    <div class="col-md-7 col-12 mb-3 mb-md-0 d-flex gap-2 flex-wrap">
        
        {{-- Tombol 1: Tambah Bahan Baku --}}
        <button id="add-button" type="button" class="add-room-btn">
            <i class="fas fa-plus me-2"></i>Tambah Bahan Baku
        </button>

        {{-- Tombol 2: Stock Opname (Pakai class add-room-btn) --}}
        <button type="button" class="add-room-btn" data-bs-toggle="modal" data-bs-target="#modalStockOpname">
            <i class="fas fa-clipboard-check me-2"></i> Stock Opname
        </button>

        {{-- Tombol 3: Riwayat Opname (Pakai class add-room-btn) --}}
        <button type="button" class="add-room-btn" onclick="showOpnameHistory()">
            <i class="fas fa-history me-2"></i> Riwayat Opname
        </button>

        <button type="button" class="add-room-btn" data-bs-toggle="modal" data-bs-target="#modalLaporanIngredients">
            <i class="fas fa-file-pdf me-2"></i> Laporan
        </button>
        
    </div>

    {{-- 2. STATUS STOK WIDGET (Ubah jadi col-md-5 biar seimbang) --}}
    <div class="col-md-5 col-12 d-flex justify-content-md-end justify-content-start">
        <div class="stock-status-compact d-flex align-items-center flex-wrap" 
             style="background-color: #F7F3E4; border: 1px solid #e0e0e0; padding: 10px 20px; border-radius: 12px; gap: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);"></div>
                {{-- Label --}}
                <div style="font-size: 14px; color: #50200C; font-weight: bold; white-space: nowrap;">
                    <i class="fas fa-chart-pie me-2"></i>Status Stok:
                </div>

                {{-- Badges --}}
                <div style="display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; align-items: center;">
                    
                    {{-- Habis --}}
                    <span style="display: flex; align-items: center;">
                        <span class="badge" style="background-color: #F2C2B8; color: #50200C !important; margin-right: 5px;">
                            <i class="fas fa-times-circle me-1"></i>HABIS
                        </span>
                        <span style="color: #50200C;">(0)</span>
                    </span>
                    
                    <span style="color: #ccc;">|</span>

                    {{-- Kritis --}}
                    <span style="display: flex; align-items: center;">
                        <span class="badge" style="background-color: #FAE8A4; color: #50200C !important; margin-right: 5px;">
                            <i class="fas fa-exclamation-triangle me-1"></i>KRITIS
                        </span>
                        <span style="color: #50200C;">(&lt;5)</span>
                    </span>

                    <span style="color: #ccc;">|</span>

                    {{-- Menipis --}}
                    <span style="display: flex; align-items: center;">
                        <span class="badge" style="background-color: #F7B267; color: #50200C; font-weight: bold; margin-right: 5px;">
                            Menipis
                        </span>
                        <span style="color: #50200C;">(&lt;20)</span>
                    </span>

                    <span style="color: #ccc;">|</span>

                    {{-- Tersedia --}}
                    <span style="display: flex; align-items: center;">
                        <span class="badge" style="background-color: #A8D5BA; color: #50200C; font-weight: bold; margin-right: 5px;">
                            Tersedia
                        </span>
                        <span style="color: #50200C;">(&gt;21)</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="professional-table-container">
        
        {{-- HEADER SECTION --}}
        <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            
            {{-- 1. JUDUL & DESKRIPSI --}}
            <div class="mb-2 mb-md-0">
                <h4><i class="fas fa-carrot me-2"></i>Persediaan Bahan Baku</h4>
                <p class="mb-0 text-muted">Kelola stok bahan dapur (Sayuran, Daging, Bumbu, dll)</p>
            </div>
            
            {{-- 2. DROPDOWN FILTER --}}
            <div class="d-flex align-items-center" style="position: relative; z-index: 100;">
                <label for="category_filter" class="me-2 fw-bold small" style="color: #50200C">Filter:</label>
                <select id="category_filter" 
                        class="form-select shadow-sm border-secondary" 
                        style="width: 220px; cursor: pointer; font-weight: 500; color: #50200C;">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" style="color: #50200C;">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- TABLE SECTION --}}
        <div class="table-responsive">
            <table id="ingredient-table" class="professional-table table table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <div class="table-footer">
            
        </div>
    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="main-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mainModalLabel">Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                {{-- Updated Buttons --}}
                <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-modal-save" id="btn-modal-save">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalStockOpname" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"> <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Form Stock Opname</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ingredients.opname') }}" method="POST">
                @csrf
                <div class="modal-body" style="background-color: #F7F3E4">
                    <div class="alert" style="background-color: #FFFF; color: #50200C">
                        <i class="fas fa-info-circle me-1"></i> Masukkan jumlah stok fisik yang real. Sistem akan otomatis menghitung selisih.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom table-sm">
                            <thead class="text-center">
                                <tr>
                                    <th>Nama Bahan</th>
                                    <th width="15%">Stok Sistem</th>
                                    <th width="20%">Stok Fisik (Real)</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ingredients as $item)
                                <tr>
                                    <td class="align-middle" style="background-color: #F7F3E4; color: #50200C;">
                                        {{ $item->name }} <small class="">({{ $item->unit }})</small>
                                        <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $loop->index }}][system_stock]" value="{{ $item->stock }}">
                                    </td>
                                    <td class="align-middle text-center" style="background-color: #F7F3E4; color: #50200C;">
                                        <strong>{{ $item->stock }}</strong>
                                    </td>
                                    <td style="background-color: #F7F3E4; color: #50200C;">
                                        <input type="number" step="0.01" name="items[{{ $loop->index }}][physical_stock]" 
                                               class="form-control form-control-sm text-center border-warning" 
                                               value="{{ $item->stock }}" required> 
                                               {{-- Default value disamakan dulu biar user gak capek ngetik 0 --}}
                                    </td>
                                    <td style="background-color: #F7F3E4; color: #50200C;">
                                        <input type="text" name="items[{{ $loop->index }}][notes]" 
                                               class="form-control form-control-sm" placeholder="Contoh: Basi / Hilang">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #F7F3E4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #50200C;">
                        <i class="fas fa-save me-2"></i>Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content" style="background-color: #F7F3E4; color: #50200C;">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Stock Opname</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalHistoryBody">
                <p class="text-center">Memuat data...</p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalLaporanIngredients" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title">Cetak Laporan Bahan Baku</h5>
                <button type="button" class="btn-close btn-close-brown" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- PENTING: Action Form harus ke route yang baru --}}
            <form action="{{ route('laporan.ingredients.pdf') }}" method="GET" target="_blank">
                
                <div class="modal-body" style="background-color: #F7F3E4; color: #50200C;">
                    <div class="mb-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="modal-footer" style="background-color: #F7F3E4;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #5c3a21; border: none;">
                        Download PDF
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showOpnameHistory() {
        // Tampilkan modal
        $('#modalHistory').modal('show');
        
        // Tampilkan loading dulu
        $('#modalHistoryBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><br>Sedang memuat data...</div>');

        // Panggil Controller via AJAX
        $.ajax({
            url: "{{ route('ingredients.history') }}",
            type: 'GET',
            success: function(response) {
                // Masukkan hasil tabel ke dalam body modal
                $('#modalHistoryBody').html(response.view);
            },
            error: function(xhr) {
                $('#modalHistoryBody').html('<p class="text-center" style="color: #50200C">Gagal memuat riwayat.</p>');
            }
        });
    }
</script>
@endpush


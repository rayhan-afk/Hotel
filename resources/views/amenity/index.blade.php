@extends('template.master')
@section('title', 'Amenity Management')
@section('content')
    <div class="container-fluid">
        
        {{-- BARIS ATAS: Tombol Tambah (Kiri) & Status Stok (Kanan) --}}
<div class="row mb-4 align-items-center">
    
    {{-- 1. BAGIAN TOMBOL (Ganti col-md-5 jadi col-md-7, dan tambah d-flex gap-2) --}}
    <div class="col-md-7 col-12 mb-3 mb-md-0 d-flex gap-2 flex-wrap">
        
        <button id="add-button" type="button" class="add-room-btn">
            <i class="fas fa-plus"></i> Tambah Amenities
        </button>

        {{-- Hapus class ms-2 karena sudah pakai gap-2 di bapaknya --}}
        <button type="button" class="add-room-btn" data-bs-toggle="modal" data-bs-target="#modalStockOpnameAmenity">
            <i class="fas fa-clipboard-check me-2"></i> Stock Opname
        </button>

        {{-- Hapus class ms-2 juga disini --}}
        <button type="button" class="add-room-btn" onclick="showAmenityHistory()">
            <i class="fas fa-history me-2"></i> Riwayat Opname
        </button>

        {{-- TOMBOL BARU: Laporan PDF (Memicu Modal Laporan) --}}
        <button type="button" class="add-room-btn" data-bs-toggle="modal" data-bs-target="#modalLaporanAmenities">
            <i class="fas fa-file-pdf me-2"></i> Laporan
        </button>
    </div>

    {{-- 2. STATUS STOK WIDGET (Kanan - Ganti col-md-7 jadi col-md-5 biar muat) --}}
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

        {{-- TABEL DATA --}}
        <div class="professional-table-container">
            <div class="table-header">
                <h4><i class="fas fa-soap me-2"></i>Manajemen Amenities</h4>
                <p>Daftar stok amenities untuk setiap kamar hotel</p>
            </div>
            
            <div class="table-responsive">
                <table id="amenity-table" class="professional-table table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col"><i class="fas fa-box me-1"></i>Nama Barang</th>
                            <th scope="col"><i class="fas fa-cubes me-1"></i>Stok</th>
                            <th scope="col"><i class="fas fa-ruler me-1"></i>Satuan</th>
                            <th scope="col"><i class="fas fa-info-circle me-1"></i>Status</th>
                            <th scope="col"><i class="fas fa-align-left me-1"></i>Keterangan</th>
                            <th scope="col"><i class="fas fa-cog me-1"></i>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="table-footer">
            </div>
        </div>
    </div>
    
    {{-- MODAL CREATE/EDIT --}}
    <div class="modal fade" id="main-modal" tabindex="-1" aria-labelledby="mainModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mainModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    {{-- Button dengan Custom Class --}}
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-modal-save" id="btn-modal-save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalStockOpnameAmenity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header border-bottom-0" style="background-color: #fdfbf7;">
                <h5 class="modal-title fw-bold" style="color: #5c3a21;">
                    <i class="fas fa-clipboard-list me-2"></i> Form Stock Opname Amenities
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="form-opname-amenity">
                @csrf
                <div class="modal-body p-4" style="background-color: #fff;">
                    
                    <div class="alert border-0 d-flex align-items-center mb-4" role="alert" 
                         style="background-color: #e3f2fd; color: #0d47a1; border-radius: 8px;">
                        <i class="fas fa-info-circle fs-5 me-3"></i>
                        <div>
                            <strong>Instruksi:</strong> Masukkan jumlah stok fisik (real) yang ada di gudang/kamar. Sistem akan otomatis menghitung selisih.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="text-center" style="border-bottom: 2px solid #f0f0f0;">
                                <tr>
                                    <th class="py-3 text-start" style="color: #5c3a21; width: 25%;">Nama Amenities</th>
                                    <th class="py-3" style="color: #5c3a21; width: 15%;">Stok Sistem</th>
                                    <th class="py-3" style="color: #5c3a21; width: 20%;">Stok Fisik (Real)</th>
                                    <th class="py-3 text-start" style="color: #5c3a21; width: 40%;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($amenities as $item)
                                <tr style="border-bottom: 1px solid #f8f9fa;">
                                    <td class="py-3">
                                        <span class="fw-bold text-dark">{{ $item->nama_barang }}</span>
                                        <small class="text-muted ms-1">({{ $item->satuan }})</small>
                                    </td>

                                    <td class="text-center py-3">
                                        <span class="fw-bold fs-6">{{ $item->stok }}</span>
                                    </td>

                                    <td class="py-3">
                                        <input type="number" 
                                               step="1" 
                                               name="stocks[{{ $item->id }}]" 
                                               class="form-control text-center fw-bold" 
                                               value="{{ $item->stok }}"
                                               min="0"
                                               style="border-color: #d4a373; color: #5c3a21;">
                                    </td>

                                    <td class="py-3">
                                        <input type="text" 
                                               name="notes[{{ $item->id }}]" 
                                               class="form-control" 
                                               placeholder="Contoh: Hilang / Rusak / Restock"
                                               style="border-color: #e9ecef;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 pb-4 pe-4 bg-white">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="background-color: #d6cfc7; border: none; color: #5c3a21;">Batal</button>
                    <button type="submit" class="btn px-4 text-white" id="btn-save-opname" style="background-color: #5c3a21;">
                        <i class="fas fa-save me-2"></i> Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Modal Kosong untuk Wadah History --}}
<div class="modal fade" id="modalHistoryAmenity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Stock Opname Amenities</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalHistoryAmenityBody">
                <p class="text-center">Memuat data...</p>
            </div>
        </div>
    </div>
</div>
{{-- MODAL PILIH TANGGAL LAPORAN --}}
<div class="modal fade" id="modalLaporanAmenities" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header" style="background-color: #5c3a21; color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-print me-2"></i>Cetak Laporan Amenities
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Form mengarah ke Route PDF --}}
            <form action="{{ route('laporan.amenities.pdf') }}" method="GET" target="_blank">
                <div class="modal-body">
                    <p class="text-muted">Pilih periode laporan yang ingin dicetak:</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-01') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background-color: #5c3a21;">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
{{-- 👇 TARUH SCRIPT DI SINI LANGSUNG (Supaya pasti terbaca) 👇 --}}
@push('scripts')
<script>
    function showAmenityHistory() {
        // 1. Buka Modal (ID harus sama dengan Modal Container di atas)
        var myModal = new bootstrap.Modal(document.getElementById('modalHistoryAmenity'));
        myModal.show();
        
        // 2. Tampilkan Loading
        $('#modalHistoryAmenityBody').html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><br>Sedang memuat data...</div>');

        // 3. Panggil Data via AJAX
        $.ajax({
            url: "{{ route('amenities.history') }}", // Pastikan route ini benar
            type: 'GET',
            success: function(response) {
                // Berhasil: Masukkan kode HTML ke dalam modal
                $('#modalHistoryAmenityBody').html(response.view);
            },
            error: function(xhr) {
                // Gagal
                console.log(xhr);
                $('#modalHistoryAmenityBody').html('<p class="text-danger text-center">Gagal memuat riwayat.</p>');
            }
        });
    }
</script>
@endpush
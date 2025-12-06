@extends('template.master')
@section('title', 'Persediaan Bahan Baku')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <button id="add-button" type="button" class="add-room-btn">
                <i class="fas fa-plus me-2"></i>Tambah Bahan Baku
            </button>
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
            
            {{-- 2. STATUS STOK WIDGET (Dari Kode Kedua) --}}
            <div class="stock-status-compact" style="background-color: #fff8e1; border: 1px solid #ffc107; padding: 8px 16px; border-radius: 4px;">
                <div style="font-size: 13px; color: #50200C; margin-bottom: 4px;">
                    <strong>📊 Status Stok:</strong>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; font-size: 12px; align-items: center;">
                    <span>
                        <span class="badge" style="background-color: #F2C2B8; color: #50200C !important;"><i class="fas fa-times-circle me-1"></i>HABIS</span>
                        <span style="color: #50200C; margin-left: 2px;">(0)</span>
                    </span>
                    <span style="color: #ddd;">|</span>
                    <span>
                        <span class="badge" style="background-color: #FAE8A4; color: #50200C !important;"></i>KRITIS</span>
                        <span style="color: #50200C; margin-left: 2px;">(&lt;5)</span>
                    </span>
                    <span style="color: #ddd;">|</span>
                    <span>
                        <span <span class="badge" style="background-color: #F7B267; color: #50200C; font-weight: bold;">Menipis</span>
                        <span style="color: #50200C; margin-left: 2px;">(&lt;20)</span>
                    </span>
                    <span style="color: #ddd;">|</span>
                    <span>
                        <span class="badge" style="background-color: #A8D5BA; color: #50200C; font-weight: bold;">Tersedia</span>
                        <span style="color: #50200C; margin-left: 2px;">(&gt;21)</span>
                    </span>
                </div>
            </div>
            
            {{-- 3. DROPDOWN FILTER --}}
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-modal-save">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection
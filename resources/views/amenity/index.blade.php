@extends('template.master')
@section('title', 'Amenity Management')
@section('content')
    <div class="container-fluid">
        
        {{-- BARIS ATAS: Tombol Tambah (Kiri) & Status Stok (Kanan) --}}
        <div class="row mb-4 align-items-center">
            
            {{-- 1. Tombol Tambah (Kiri) --}}
            <div class="col-md-5 col-12 mb-3 mb-md-0">
                <button id="add-button" type="button" class="add-room-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Amenities
                </button>
            </div>

            {{-- 2. Status Stok Widget (Kanan) --}}
            <div class="col-md-7 col-12 d-flex justify-content-md-end justify-content-start">
                <div class="stock-status-compact d-flex align-items-center flex-wrap" 
                     style="background-color: #F7F3E4; border: 1px solid #e0e0e0; padding: 10px 20px; border-radius: 12px; gap: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                    
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
@endsection
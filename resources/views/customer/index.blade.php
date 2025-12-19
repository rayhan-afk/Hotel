@extends('template.master')
@section('title', 'Daftar Customer')

@section('content')
<div class="container-fluid">
    {{-- HEADER, SEARCH & ADD BUTTON --}}
    <div class="row mt-4 mb-3 align-items-center">
        <div class="col-md-5" style="color: #50200C">
            <h3 class="fw-bold"><i class="fas fa-users me-2"></i>Daftar Tamu</h3>
            <p class="mb-0 text-muted">Kelola data tamu dan status member.</p>
        </div>

        <div class="col-md-7">
            <div class="d-flex justify-content-md-end align-items-center gap-2 mt-3 mt-md-0">
                
                {{-- 1. Search Form --}}
                <div class="input-group shadow-sm" style="max-width: 300px;">
                    <span class="input-group-text bg-white border-end-0" style="color: #50200C">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" 
                           id="searchInput" 
                           placeholder="Cari Nama / No. HP..." 
                           name="q">
                </div>

                {{-- 2. TOMBOL TAMBAH (BARU) --}}
                <button id="add-button" class="btn text-white shadow-sm" 
                        style="background-color: #50200C; border-color: #50200C;">
                    <i class="fas fa-plus me-2"></i>Tambah Tamu
                </button>
            </div>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="customer-table" class="table table-hover align-middle mb-0 w-100">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="px-4 py-3 text-center" width="5%">#</th>
                                    <th class="py-3 text-center" width="8%">Avatar</th>
                                    <th class="py-3">Nama Lengkap</th>
                                    
                                    {{-- KOLOM BARU: GRUP CUSTOMER --}}
                                    <th class="py-3 text-center" width="10%">Grup</th>
                                    
                                    <th class="py-3">Kontak (HP & Email)</th>
                                    <th class="py-3">Pekerjaan</th>
                                    <th class="py-3" width="20%">Alamat</th>
                                    <th class="px-4 py-3 text-center" width="12%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Isi tabel akan dimuat otomatis oleh Datatable (AJAX) --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 border-0"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    {{-- Panggil script customer.js --}}
    {{-- Pastikan js ini sudah di-compile atau ada di public --}}
    <script src="{{ asset('js/pages/customer.js') }}"></script>
@endsection
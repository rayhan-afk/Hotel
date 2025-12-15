@extends('template.master')
@section('title', 'Daftar Customer')

@section('content')
<div class="container-fluid">
    {{-- HEADER & SEARCH --}}
    <div class="row mt-4 mb-3 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark"><i class="fas fa-users me-2"></i>Daftar Tamu</h3>
            <p class="text-muted mb-0">Kelola data tamu, kontak, dan riwayat kunjungan.</p>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-md-end gap-2 mt-3 mt-md-0">
                {{-- Search Form (ID "searchInput" ditambahkan agar terdeteksi JS) --}}
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0" 
                           id="searchInput" 
                           placeholder="Cari Nama / No. HP..." 
                           name="q" 
                           value="{{ request()->input('q') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        {{-- TABEL UTAMA (Di-handle oleh customer.js) --}}
                        <table id="customer-table" class="table table-hover align-middle mb-0 w-100">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="px-4 py-3 text-center" width="5%">#</th>
                                    <th class="py-3 text-center" width="10%">Avatar</th>
                                    <th class="py-3">Nama Lengkap</th>
                                    <th class="py-3">Kontak (HP & Email)</th>
                                    <th class="py-3">Pekerjaan</th>
                                    <th class="py-3" width="25%">Alamat</th>
                                    <th class="px-4 py-3 text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Isi tabel akan dimuat otomatis oleh Datatable (AJAX) --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination sudah otomatis dihandle Datatable, footer opsional --}}
                <div class="card-footer bg-white py-3 border-0"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    {{-- Panggil script customer.js --}}
    <script src="{{ asset('js/pages/customer.js') }}"></script>
@endsection
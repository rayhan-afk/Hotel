@extends('template.master')
@section('title', 'Laporan Ruang Rapat')

@section('content')
<div class="container-fluid">
    
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12">
            <h2><i class="fas fa-history me-2"></i>Laporan Ruang Rapat</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="professional-table-container">
                
                {{-- FILTER SECTION --}}
                <div class="table-header p-3" style="position: relative; z-index: 2;">
                    <form id="filter-form">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_mulai" class="form-label text-dark fs-5 fw-bold">Periode Dari Tanggal</label>
                                <input type="date" id="tanggal_mulai" class="form-control shadow-sm form-control-lg">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_selesai" class="form-label text-dark fs-5 fw-bold">Sampai Tanggal</label>
                                <input type="date" id="tanggal_selesai" class="form-control shadow-sm form-control-lg">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btn-filter" class="btn w-100 btn-lg text-white shadow-sm btn-brown">
                                        Search
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-secondary btn-lg shadow-sm" title="Reset Filter">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATATABLES --}}
                <div class="table-responsive mt-3">
                    <table id="laporan-rapat-table" class="professional-table table table-hover" style="width: 100%;">
                        <thead style="background-color: #f7f3e8;">
                            <tr>
                                <th>Instansi/Perusahaan</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Paket</th>
                                <th>Jml Peserta</th> {{-- Kolom Baru --}}
                                <th>Total Bayar</th> {{-- Kolom Baru --}}
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                
                {{-- FOOTER: TOMBOL EXPORT ADA DISINI --}}
                <div class="table-footer d-flex justify-content-end align-items-center p-4">
                   
                   {{-- TOMBOL EXPORT COKLAT DI BAWAH --}}
                   <button type="button" id="btn-export" class="btn btn-lg text-white shadow-sm btn-brown px-4">
                       <i class="fas fa-file-excel me-2"></i> Export Excel
                   </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<style>
    /* Helper Style Warna Coklat */
    .btn-brown {
        background-color: #50200C !important;
        border-color: #50200C !important;
    }
    .btn-brown:hover {
        background-color: #3d1909 !important;
        border-color: #3d1909 !important;
    }
    
    /* Fix Overlay Table */
    .professional-table-container .table-header::before { display: none !important; content: none !important; }
    .table-header form { position: relative; z-index: 10; }
</style>
@endsection
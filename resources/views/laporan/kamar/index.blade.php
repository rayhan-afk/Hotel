@extends('template.master')
@section('title', 'Laporan Reservasi Kamar')

@section('content')
<div class="container-fluid">
    
    {{-- HEADER --}}
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12" style="color: #50200C;">
            <h2><i class="fas fa-bed me-2"></i>Laporan Kamar Hotel</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="professional-table-container">
                
                {{-- FILTER SECTION --}}
                <div class="table-header p-3" style="position: relative; z-index: 2;">
                    <form id="filter-form">
                        <div class="row align-items-end" style="color: #50200C;">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label fs-5 fw-bold">Periode Dari Tanggal</label>
                                <input style="color: #50200C;" type="date" id="start_date" class="form-control shadow-sm form-control-lg" name="start_date">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label fs-5 fw-bold">Sampai Tanggal</label>
                                <input style="color: #50200C;" type="date" id="end_date" class="form-control shadow-sm form-control-lg" name="end_date">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btn-filter" class="btn w-100 btn-lg text-white shadow-sm btn-brown">
                                        Cari
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-secondary btn-lg shadow-sm" title="Reset Filter">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL --}}
                <div class="table-responsive mt-3">
                    <table id="laporan-kamar-table" class="professional-table table table-hover" style="width: 100%;">
                        <thead style="background-color: #f7f3e8;">
                            <tr>
                                <th>Tamu</th>
                                <th>Kamar</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th class="text-center">Sarapan</th>
                                <th class="text-end">Total Harga</th>
                                <th class="text-center">Status</th>
                                {{-- [UPDATE] Penambahan Kolom Aksi --}}
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                
                {{-- FOOTER --}}
                <div class="table-footer d-flex justify-content-end align-items-center p-4">
                   <button type="button" id="btn-export-kamar" class="btn btn-lg text-white shadow-sm btn-brown px-4">
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
    .btn-brown {
        background-color: #50200C !important;
        border-color: #50200C !important;
    }
    .btn-brown:hover {
        background-color: #3d1909 !important;
        border-color: #3d1909 !important;
    }
    .professional-table-container .table-header::before { display: none !important; content: none !important; }
    .table-header form { position: relative; z-index: 10; }
</style>
@endsection
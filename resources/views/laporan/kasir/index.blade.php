@extends('template.master')
@section('title', 'Laporan Kasir')

@section('content')
<div class="container-fluid">
    
    {{-- HEADER JUDUL --}}
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12" style="color: #50200C;">
            <h2><i class="fas fa-cash-register me-2"></i>Laporan Transaksi Kasir</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="professional-table-container">
                
                {{-- FILTER SECTION --}}
                <div class="table-header p-3" style="position: relative; z-index: 2;">
                    <form id="filterForm">
                        <div class="row align-items-end" style="color: #50200C;">
                            {{-- Input Tanggal Mulai --}}
                            <div class="col-md-4 mb-3">
                                <label for="startDate" class="form-label fs-5 fw-bold">Periode Dari Tanggal</label>
                                <input style="color: #50200C;" type="date" name="start_date" id="startDate" value="{{ date('Y-m-d') }}" class="form-control shadow-sm form-control-lg">
                            </div>

                            {{-- Input Tanggal Selesai --}}
                            <div class="col-md-4 mb-3">
                                <label for="endDate" class="form-label fs-5 fw-bold">Sampai Tanggal</label>
                                <input style="color: #50200C;" type="date" name="end_date" id="endDate" value="{{ date('Y-m-d') }}" class="form-control shadow-sm form-control-lg">
                            </div>

                            {{-- Tombol Filter & Reset --}}
                            <div class="col-md-4 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="button" id="btnFilter" class="btn w-100 btn-lg text-white shadow-sm btn-brown">
                                        Cari
                                    </button>
                                    <button type="button" id="btnReset" class="btn btn-secondary btn-lg shadow-sm" title="Reset Filter">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATATABLES --}}
                <div class="table-responsive mt-3">
                    <table id="tableLaporanPos" 
                           data-route="{{ route('laporan.pos.index') }}" 
                           class="professional-table table table-hover" 
                           style="width: 100%;">
                        <thead style="background-color: #f7f3e8;">
                            <tr>
                                <th>No</th>
                                <th>Invoice</th>
                                <th>Waktu</th>
                                <th>Menu Terjual</th>
                                <th>Metode</th>
                                <th class="text-end">Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data diisi AJAX --}}
                        </tbody>
                        <tfoot style="background-color: #fff8f0; font-weight: bold; border-top: 2px solid #50200C;">
                            <tr>
                                <td colspan="5" class="text-end py-3" style="color: #50200C;">Total Omset Halaman Ini:</td>
                                <td class="text-end py-3 pe-3" id="pageTotal" style="color: #50200C;">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                {{-- FOOTER: TOMBOL EXPORT --}}
                <div class="table-footer d-flex justify-content-end align-items-center p-4">
                    <button type="button" id="btnExport" 
                            data-route-export="{{ route('laporan.pos.export') }}"
                            class="btn btn-lg text-white shadow-sm btn-brown px-4">
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
    /* Helper Style Warna Coklat (Sesuai Laporan Rapat) */
    .btn-brown {
        background-color: #50200C !important;
        border-color: #50200C !important;
    }
    .btn-brown:hover {
        background-color: #3d1909 !important;
        border-color: #3d1909 !important;
    }
    
    /* Input Styling Override */
    .form-control:focus {
        border-color: #50200C;
        box-shadow: 0 0 0 0.25rem rgba(80, 32, 12, 0.25);
    }

    /* Fix Overlay Table agar form bisa diklik */
    .professional-table-container .table-header::before { display: none !important; content: none !important; }
    .table-header form { position: relative; z-index: 10; }
</style>
@endsection
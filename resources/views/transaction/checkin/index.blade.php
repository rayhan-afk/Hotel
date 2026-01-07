@extends('template.master')
@section('title', 'Check In & Check Out Tamu') 

@section('content')
<div class="container-fluid">
    
    {{-- HEADER --}}
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12">
            <h2 style="color:#50200C">
                <i class="fas fa-exchange-alt me-2" style="color:#50200C"></i> Check In & Check Out
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="professional-table-container">

                {{-- TABLE HEADER --}}
                <div class="table-header">
                    <h4><i class="fas fa-bed me-2"></i>Data Tamu Menginap (Active)</h4>
                    <p>Daftar tamu yang sedang menginap. Klik tombol <span class="text-danger fw-bold">Merah</span> pada kolom aksi untuk melakukan <b>Check Out</b>.</p>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table id="checkin-table" class="professional-table table table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                {{-- Penyesuaian Lebar Kolom (Total 100%) --}}
                                <th scope="col" style="width: 5%;"><i class="fas fa-hashtag me-1"></i>No</th>
                                <th scope="col" style="width: 17%;"><i class="fas fa-user me-1"></i>Tamu</th>
                                <th scope="col" style="width: 10%;"><i class="fas fa-bed me-1"></i>Kamar</th>
                                <th scope="col" style="width: 12%;"><i class="fas fa-calendar-check me-1"></i>Check-In</th>
                                <th scope="col" style="width: 12%;"><i class="fas fa-calendar-times me-1"></i>Check-Out</th>
                                
                                {{-- Extra Bed & Breakfast DIHAPUS --}}

                                <th scope="col" style="width: 8%;" class="text-center"><i class="fas fa-utensils me-1"></i>Srp</th> 
                                <th scope="col" style="width: 12%;" class="text-end"><i class="fas fa-dollar-sign me-1"></i>Total</th>
                                <th scope="col" style="width: 12%;" class="text-end text-danger"><i class="fas fa-hand-holding-usd me-1"></i>Sisa</th>
                                <th scope="col" style="width: 12%;" class="text-center"><i class="fas fa-info-circle me-1"></i>Status</th>
                                <th scope="col" style="width: 10%;" class="text-center"><i class="fas fa-cogs me-1"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                {{-- FOOTER --}}
                <div class="table-footer"></div>

            </div>

        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editCheckinModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #50200C">Edit Data Reservasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editCheckinBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI CHECK OUT --}}
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title"><i class="fas fa-sign-out-alt me-2" style="color: #50200C"></i>Konfirmasi Check Out</h5>
                <button type="button" class="btn-close custom-brown" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" style="background-color: #F7F3E4; color: #50200C">
                <div class="mb-3" style="color: #C49A6C">
                    <i class="fas fa-exclamation-circle fa-4x"></i>
                </div>
                
                <h4 class="fw-bold mb-2">Check Out Tamu Ini?</h4>
                <p class="mb-0">Tamu: <span id="checkoutCustomerName" class="fw-bold">-</span></p>
                <p class=" ">Kamar: <span id="checkoutRoomNumber" class="fw-bold">-</span></p>
                
                <div class="alert alert-light border border-brown d-flex align-items-center" role="alert">
                    <i class="fas fa-info-circle me-2" style="color: #C49A6C"></i>
                    <small class="text-start" style="color: #50200C">Pastikan kunci kamar sudah dikembalikan dan seluruh pembayaran telah dilunasi.</small>
                </div>
            </div>
            <div class="modal-footer justify-content-center" style="background-color: #F7F3E4">
                <button type="button" style="background-color: #F2C2B8; color: #50200C;" class="btn px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" style="background-color: #A8D5BA; color: #50200C;" class="btn px-4 fw-bold" id="btn-confirm-checkout">
                    <i class="fas fa-check me-2"></i>Ya, Check Out
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    <style>
        .professional-table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-top: 20px;
        }
        .table-header { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .professional-table thead th { background-color: #f7f3e8; color: #333; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px; }
        .table-footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; color: #6c757d; }
        .professional-table-container .table-header::before { display: none !important; }
        
        /* Tambahan agar badge tidak terlalu mepet */
        .professional-table td .badge { margin: 0 1px; }
    </style>
    
    {{-- PENTING: Pastikan JS dipanggil disini jika tidak ada di template master --}}
    {{-- <script src="{{ asset('js/checkin.js') }}"></script> --}}
@endsection
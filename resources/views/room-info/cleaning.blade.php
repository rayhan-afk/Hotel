@extends('template.master')
@section('title', 'Kamar Dibersihkan')

@section('content')
    <div class="container-fluid">
        
        <div class="row my-2 mt-4 ms-1">
            <div class="col-lg-12">
                <h2 style="color:#50200C">
                    <i class="fas fa-broom me-2"></i> Kamar Sedang Dibersihkan
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="professional-table-container">
                    
                    <div class="table-header">
                        <h4><i class="fas fa-clipboard-list me-2"></i>Daftar Antrian Housekeeping</h4>
                        <p>Daftar kamar yang statusnya <b>"Cleaning"</b>. Kamar ini belum muncul di menu <i>Kamar Tersedia</i> sampai Anda menyelesaikannya di sini.</p>
                    </div>

                    {{-- Table --}}
                    <div class="table-responsive">
                        <table id="cleaning-table" class="professional-table table table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 5%;"><i class="fas fa-hashtag me-1"></i>No</th>
                                    <th scope="col" style="width: 20%;"><i class="fas fa-door-open me-1"></i>Nomor Kamar</th>
                                    <th scope="col" style="width: 25%;"><i class="fas fa-bed me-1"></i>Tipe Kamar</th>
                                    <th scope="col" style="width: 20%;" class="text-center"><i class="fas fa-info-circle me-1"></i>Status</th>
                                    <th scope="col" style="width: 30%;" class="text-center"><i class="fas fa-cogs me-1"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> 
                            Klik tombol <b>"Selesai"</b> jika kamar sudah bersih. Status akan berubah menjadi <b>Available</b>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI SELESAI MEMBERSIHKAN (BARU) --}}
    <div class="modal fade" id="finishCleaningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2" style="color: #50200C"></i>Konfirmasi Pembersihan</h5>
                    <button type="button" class="btn-close custom-brown" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4" style="background-color: #F7F3E4; color: #50200C">
                    <div class="mb-3" style="color: #C49A6C">
                        <i class="fas fa-broom fa-4x"></i>
                    </div>
                    
                    <h4 class="fw-bold mb-2" style="color: #50200C">Selesai Membersihkan?</h4>
                    <p class="mb-0">Nomor Kamar: <span id="cleaningRoomNumber" class="fw-bold fs-5">-</span></p>
                    <p class=" ">Tipe: <span id="cleaningRoomType" class="fw-bold">-</span></p>
                    
                    <div class="alert alert-light border border-success d-flex align-items-center" role="alert">
                        <i class="fas fa-info-circle me-2" style="color: #C49A6C"></i>
                        <small class="text-start" style="color: #50200C">
                            Kamar akan dipindahkan ke status <b>Available (Tersedia)</b> dan siap untuk dipesan kembali.
                        </small>
                    </div>
                </div>
                <div class="modal-footer justify-content-center" style="background-color: #F7F3E4">
                    <button type="button" style="background-color: #F2C2B8; color: #50200C;" class="btn px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" style="background-color: #A8D5BA; color: #50200C;" class="btn px-4 fw-bold" id="btn-confirm-finish">
                        <i class="fas fa-check me-2"></i>Ya, Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="{{ asset('js/pages/kamar-dibersihkan.js') }}"></script>

    <style>
        .professional-table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-top: 20px;
        }
        .table-header { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .professional-table thead th { 
            background-color: #f7f3e8; 
            color: #50200C; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 0.85rem; 
            padding: 12px; 
        }
        .table-footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; color: #6c757d; }
    </style>
@endsection
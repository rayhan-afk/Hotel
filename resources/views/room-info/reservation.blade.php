@extends('template.master')
@section('title', 'Reservasi Kamar')

@section('content')
<div class="container-fluid">
    
    {{-- HEADER --}}
    <div class="row my-2 mt-4 ms-1" style="color: #50200C">
        <div class="col-lg-12">
            <h2><i class="fas fa-calendar-alt me-2"></i> Reservasi Kamar</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="professional-table-container">

                {{-- TABLE HEADER --}}
                <div class="table-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><i class="fas fa-clock me-2"></i>Daftar Reservasi Mendatang</h4>
                        <p class="mb-0 text-muted">Daftar tamu yang akan Check-in dalam waktu dekat.</p>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table id="reservation-table" class="professional-table table table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 5%;"><i class="fas fa-hashtag me-1"></i>No</th>
                                <th scope="col" style="width: 20%;"><i class="fas fa-user me-1"></i>Tamu</th>
                                <th scope="col" style="width: 15%;"><i class="fas fa-bed me-1"></i>Kamar</th>
                                <th scope="col" style="width: 12%;"><i class="fas fa-calendar-check me-1"></i>Check-In</th>
                                <th scope="col" style="width: 12%;"><i class="fas fa-calendar-times me-1"></i>Check-Out</th>
                                <th scope="col" style="width: 8%;" class="text-center"><i class="fas fa-utensils me-1"></i>Sarapan</th>
                                <th scope="col" style="width: 13%;" class="text-end"><i class="fas fa-dollar-sign me-1"></i>Total Harga</th>
                                <th scope="col" style="width: 10%;" class="text-center"><i class="fas fa-info-circle me-1"></i>Status</th>
                                <th scope="col" style="width: 5%;" class="text-center"><i class="fas fa-cog me-1"></i>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="table-footer"></div>
            </div>

        </div>
    </div>
</div>

{{-- [BARU] MODAL CANCEL RESERVATION --}}
<div class="modal fade" id="cancelReservationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-ban me-2"></i>Batalkan Reservasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Form ini nanti Action-nya diisi via JS --}}
            <form id="cancelReservationForm" action="" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    
                    <div class="alert alert-warning d-flex align-items-center mb-4 border-0 shadow-sm" role="alert">
                        <i class="fas fa-exclamation-triangle me-3 fa-2x"></i>
                        <div>
                            <strong>Perhatian!</strong> Pembatalan ini bersifat permanen dan status kamar akan kembali menjadi "Available".
                        </div>
                    </div>

                    {{-- Pilihan Alasan --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Alasan Pembatalan</label>
                        <select name="cancel_reason" class="form-select shadow-sm" required>
                            <option value="" disabled selected>-- Pilih Alasan --</option>
                            <option value="Guest Request">Permintaan Tamu (Guest Request)</option>
                            <option value="Booker Request">Permintaan Pemesan (Booker Request)</option>
                            <option value="Duplicate Booking">Booking Ganda (Duplicate)</option>
                            <option value="No Show">Tamu Tidak Datang (No Show)</option>
                            <option value="Other">Lainnya (Other)</option>
                        </select>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Catatan Tambahan</label>
                        <textarea name="cancel_notes" class="form-control shadow-sm" rows="3" placeholder="Contoh: Tamu sakit, reschedule, dll..."></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 bg-white">
                    <button type="button" class="btn btn-light shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger shadow-sm fw-bold px-4">
                        <i class="fas fa-times-circle me-1"></i> Konfirmasi Batal
                    </button>
                </div>
            </form>
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
    </style>
    
    {{-- Memanggil Script JS Khusus Reservasi --}}
    <script src="{{ asset('js/pages/reservation.js') }}"></script>
@endsection
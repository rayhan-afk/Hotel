@extends('template.master')
@section('title', 'Room Types')
@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <button id="add-button" type="button" class="add-room-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Tipe Baru
                </button>
            </div>
        </div>

        <div class="professional-table-container">
            <div class="table-header">
                <h4><i class="fas fa-home me-2"></i>Manajemen Tipe Kamar</h4>
                <p>Kelola berbagai jenis kamar dan atur harga spesial disini</p>
            </div>

            <div class="table-responsive">
                <table id="type-table" class="professional-table table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i>#</th>
                            <th><i class="fas fa-tag me-1"></i>Nama</th>
                            <th><i class="fas fa-info-circle me-1"></i>Informasi</th>
                            <th><i class="fas fa-cog me-1"></i>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="priceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Atur Harga Spesial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body" style="background-color: #F7F3E4">
                    <form id="priceForm">
                        <input type="hidden" id="price_type_id" name="type_id">
                        
                        <div class="alert alert-light border-start border-5 border-secondary shadow-sm" role="alert"
                        style="color: #50200C">
                            <small class=" ">
                                <i class="fas fa-info-circle me-1"></i> 
                                Jika kolom dikosongkan, sistem akan otomatis menggunakan <b>Harga Dasar Kamar</b>.
                            </small>
                        </div>

                        <table class="table table-bordered table-hover align-middle">
                            <thead style="background-color: #F7F3E4; color: #50200C; border-top: 3px solid;">
                                <tr>
                                    <th width="30%" class="text-uppercase font-weight-bold" style="background-color: #F7F3E4;">Grup Customer</th>
                                    <th width="35%" class="text-uppercase font-weight-bold" style="background-color: #F7F3E4;">Weekday <small class="text-muted">(Senin-Kamis)</small></th>
                                    <th width="35%" class="text-uppercase font-weight-bold" style="background-color: #F7F3E4;">Weekend <small class="text-muted">(Jumat-Sabtu)</small></th>
                                </tr>
                            </thead>
                            <tbody id="priceTableBody"></tbody>
                        </table>
                    </form>
                </div>
                
                <div class="modal-footer" style="background-color: #F7F3E4">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modal-save" id="btn-modal-save">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('template.master')
@section('title', 'Amenity Management')
@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <button id="add-button" type="button" class="add-room-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Amenities
                </button>
            </div>
        </div>

        <div class="professional-table-container">
            <div class="table-header">
                <h4><i class="fas fa-soap me-2"></i>Manajemen Amenities</h4>
                <p>Daftar stok amenities untuk setiap kamar hotel</p>
            </div>
            <div class="table-responsive">
                <table id="amenity-table" class="professional-table table" style="width: 100%;">
                    <thead>
                        <tr>
                            {{-- 1. No --}}
                            <th scope="col">No</th>
                            
                            {{-- 2. Nama Barang --}}
                            <th scope="col">
                                <i class="fas fa-box me-1"></i>Nama Barang
                            </th>
                            
                            {{-- 3. Stok --}}
                            <th scope="col">
                                <i class="fas fa-cubes me-1"></i>Stok
                            </th>

                            {{-- 4. Satuan --}}
                            <th scope="col">
                                <i class="fas fa-ruler me-1"></i>Satuan
                            </th>

                            {{-- 5. Status (BARU) --}}
                            <th scope="col">
                                <i class="fas fa-info-circle me-1"></i>Status
                            </th>
                            
                            {{-- 6. Keterangan --}}
                            <th scope="col">
                                <i class="fas fa-align-left me-1"></i>Keterangan
                            </th>
                            
                            {{-- 7. Action --}}
                            <th scope="col">
                                <i class="fas fa-cog me-1"></i>Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="table-footer">
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="main-modal" tabindex="-1" aria-labelledby="mainModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mainModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btn-modal-save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
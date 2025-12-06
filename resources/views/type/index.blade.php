@extends('template.master')
@section('title', 'Room Types')
@section('content')
    <div class="container-fluid">
        <!-- Add Type Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button id="add-button" type="button" class="add-room-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Tipe Baru
                </button>
            </div>
        </div>

        <!-- Professional Table Container -->
        <div class="professional-table-container">
            <!-- Table Header -->
            <div class="table-header">
                <h4><i class="fas fa-home me-2"></i>Manajemen Tipe Kamar</h4>
                <p>Kelola berbagai jenis kamar dan informasinya</p>
            </div>

            <!-- Professional Table -->
            <div class="table-responsive">
                <table id="type-table" class="professional-table table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col">
                                <i class="fas fa-hashtag me-1"></i>#
                            </th>
                            <th scope="col">
                                <i class="fas fa-tag me-1"></i>Nama
                            </th>
                            <th scope="col">
                                <i class="fas fa-info-circle me-1"></i>Informasi
                            </th>
                            <th scope="col">
                                <i class="fas fa-cog me-1"></i>Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTable will populate this -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                <h3><i class="fas fa-home me-2"></i>Tipe Kamar</h3>
            </div>
        </div>
    </div>
@endsection

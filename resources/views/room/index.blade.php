@extends('template.master')
@section('title', 'Room Management')

@section('content')
    {{-- Mengambil data Type secara langsung untuk filter dropdown --}}
    @php
        $types = \App\Models\Type::all();
    @endphp

    <div class="container-fluid">
        <!-- Add Room Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button id="add-button" type="button" class="add-room-btn">
                    <i class="fas fa-plus me-2"></i>
                    Tambah Kamar Baru
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="professional-table-container">
            <!-- Table Header -->
            <div class="table-header">
                <h4><i class="fas fa-bed me-2"></i>Manajemen Kamar</h4>
                <p>Kelola data kamar, fasilitas, luas area, dan informasi lainnya.</p>
            </div>

            <!-- Filters Section -->
            <div class="filters-section" style="background-color: #F7F3E4">
                <div class="filters-title" style="color: #50200C">
                    <i class="fas fa-filter me-2" style="color: #50200C"></i>
                    Filter Kamar
                </div>
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="mb-3">
                            <label for="type" class="form-label" style="color: #50200C">
                                <i class="fas fa-home me-1" style="color: #50200C"></i>Tipe Kamar
                            </label>
                            <select id="type" class="form-select" aria-label="Choose type" style="color: #50200C">
                                <option selected value="All">Semua Tipe</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table id="room-table" class="professional-table table table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;">
                                <i class="fas fa-hashtag me-1"></i>No
                            </th>
                            <th scope="col" style="width: 15%;">
                                <i class="fas fa-tag me-1"></i>Nama
                            </th>
                            <th scope="col" style="width: 10%;">
                                <i class="fas fa-home me-1"></i>Tipe
                            </th>
                            <th scope="col" style="width: 10%;">
                                <i class="fas fa-ruler-combined me-1"></i>Luas
                            </th>
                            {{-- Fasilitas Umum --}}
                            <th scope="col" style="width: 15%;">
                                <i class="fas fa-list me-1"></i>Fasilitas
                            </th>
                            {{-- Fasilitas Kamar Mandi --}}
                            <th scope="col" style="width: 15%;">
                                <i class="fas fa-bath me-1"></i>Kamar Mandi
                            </th>
                            <th scope="col" style="width: 5%;">
                                <i class="fas fa-user me-1"></i>Kapasitas
                            </th>
                            <th scope="col" style="width: 15%;">
                                <i class="fas fa-dollar-sign me-1"></i>Harga
                            </th>
                            {{-- Kolom Status Dihapus --}}
                            <th scope="col" style="width: 10%;">
                                <i class="fas fa-cog me-1"></i>Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTable will populate this via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-footer">
                
            </div>
        </div>
    </div>
    
    <!-- Modal Container (Penting untuk Create/Edit Form) -->
    <div class="modal fade" id="main-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Form akan di-load di sini via AJAX --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-modal-save">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
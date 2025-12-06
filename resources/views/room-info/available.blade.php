@extends('template.master')
@section('title', 'Kamar Tersedia')

@section('content')
    @php
        $types = \App\Models\Type::all();
    @endphp

    <div class="container-fluid">
        
        {{-- Table Container --}}
        <div class="professional-table-container">
            
            {{-- Table Header --}}
            <div class="table-header">
                <h4><i class="fas fa-check-circle me-2"></i>Kamar Tersedia</h4>
                <p>Daftar kamar yang siap untuk Check-in tamu baru hari ini.</p>
            </div>

            {{-- Filters Section --}}
            <div class="filters-section" style="background-color: #F7F3E4">
                <div class="filters-title" style="color: #50200C">
                    <i class="fas fa-filter me-2" style="color: #50200C"></i>Filter Kamar
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

            {{-- Table --}}
            <div class="table-responsive">
                <table id="available-room-table" class="professional-table table table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;"><i class="fas fa-hashtag me-1"></i>No</th>
                            <th scope="col" style="width: 10%;"><i class="fas fa-door-open me-1"></i>Nomor</th>
                            <th scope="col" style="width: 15%;"><i class="fas fa-tag me-1"></i>Nama</th>
                            <th scope="col" style="width: 10%;"><i class="fas fa-home me-1"></i>Tipe</th>
                            <th scope="col" style="width: 10%;"><i class="fas fa-ruler-combined me-1"></i>Luas</th>
                            {{-- Fasilitas dipisah --}}
                            <th scope="col" style="width: 15%;"><i class="fas fa-couch me-1"></i>Fasilitas</th>
                            <th scope="col" style="width: 15%;"><i class="fas fa-bath me-1"></i>Kamar Mandi</th>
                            
                            <th scope="col" style="width: 5%;"><i class="fas fa-user me-1"></i>Kapasitas</th>
                            <th scope="col" style="width: 15%;"><i class="fas fa-dollar-sign me-1"></i>Harga</th>
                            <th scope="col" style="width: 10%;"><i class="fas fa-info-circle me-1"></i>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="table-footer">

            </div>
        </div>
    </div>
@endsection

@section('footer')
    {{-- Style CSS --}}
    <style>
        .professional-table-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-top: 20px;
        }
        .table-header { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .filters-section { background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e9ecef; }
        .filters-title { font-weight: 600; color: #495057; margin-bottom: 10px; font-size: 0.9rem; text-transform: uppercase; }
        .professional-table thead th { background-color: #f7f3e8; color: #333; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 12px; }
        .table-footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; color: #6c757d; }
    </style>
@endsection
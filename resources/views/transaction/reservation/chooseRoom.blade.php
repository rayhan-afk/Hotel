@extends('template.master')
@section('title', 'Pilih Kamar Reservasi')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        .wrapper {
            max-width: 400px;
        }

        .demo-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        /* Custom Style untuk mempercantik tampilan */
        .card-room {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e0e0e0;
        }
        .card-room:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        .btn-choose {
            background-color: #50200C;
            color: #F7F3E4;
            border: none;
            transition: all 0.2s;
        }
        .btn-choose:hover {
            background-color: #50200C;
            color: #F7F3E4;
            transform: scale(1.01);
        }
        .text-brown {
            color: #8B4513; /* Warna Coklat */
        }
        .btn-brown {
            background-color: #8B4513;
            color: white;
            border: none;
        }
        .btn-brown:hover {
            background-color: #A0522D;
            color: white;
        }
    </style>
@endsection
@section('content')
    @include('transaction.reservation.progressbar')
    
    {{-- Ambil data tipe kamar untuk filter --}}
    @php
        $types = \App\Models\Type::all();
    @endphp

    <div class="container mt-4 mb-5" style="background-color: #F7F3E4;">
        <div class="row justify-content-center">
            {{-- Kolom Kiri: Daftar Kamar --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        {{-- Header Informasi Pencarian --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1" style="color:#50200C">
                                    <i class="fas fa-bed me-2" style="color:#50200C"></i> {{ $roomsCount }} Ruangan Tersedia
                                </h4>
                                <p class="mb-0 small" style="color:#50200C">
                                    Untuk <span class="fw-bold" style="color:#50200C">{{ request()->input('count_person') }} {{ Helper::plural('Orang', request()->input('count_person')) }}</span>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-calendar-alt me-1"></i> {{ Helper::dateFormat(request()->input('check_in')) }} 
                                    <i class="fas fa-arrow-right mx-1"></i> 
                                    {{ Helper::dateFormat(request()->input('check_out')) }}
                                </p>
                            </div>
                        </div>
                        
                        <hr class="my-4" style="color:#50200C">
                        
                        {{-- Form Filter & Sorting --}}
                        <form method="GET" action="{{ route('transaction.reservation.chooseRoom', ['customer' => $customer->id]) }}" class="mb-4">
                            {{-- Input Hidden untuk Data Step Sebelumnya --}}
                            <input type="hidden" name="count_person" value="{{ request()->input('count_person') }}">
                            <input type="hidden" name="check_in" value="{{ request()->input('check_in') }}">
                            <input type="hidden" name="check_out" value="{{ request()->input('check_out') }}">
                            
                            <div class="row g-3 align-items-end">
                                {{-- Filter Tipe Kamar --}}
                                <div class="col-md-5">
                                    <label for="type_id" class="form-label small fw-bold text-uppercase" style="color:#50200C">Tipe Kamar</label>
                                    <select class="form-select shadow-sm" style="color:#50200C" id="type_id" name="type_id" aria-label="Pilih Tipe">
                                        <option value="" style="color:#50200C">Semua Tipe</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" style="color:#50200C" @if(request()->input('type_id') == $type->id) selected @endif>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Sorting Harga --}}
                                <div class="col-md-5">
                                    <label for="sort_price" class="form-label small fw-bold text-uppercase" style="color:#50200C">Urutkan Harga</label>
                                    <select class="form-select shadow-sm" style="color:#50200C" id="sort_price" name="sort_price" aria-label="Urutkan Harga">
                                        <option value="ASC" style="color:#50200C" @if (request()->input('sort_price') == 'ASC') selected @endif>Termurah ke Termahal</option>
                                        <option value="DESC" style="color:#50200C" @if (request()->input('sort_price') == 'DESC') selected @endif>Termahal ke Termurah</option>
                                    </select>
                                </div>

                                {{-- Tombol Cari --}}
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-hotel-primary w-100 shadow-sm">
                                        <i class="fas fa-filter" style="color:#F7F3E4"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Daftar Kamar (Looping) --}}
                        <div class="d-grid gap-4">
                            @forelse ($rooms as $room)
                                <div class="card card-room rounded overflow-hidden">
                                    <div class="row g-0">
                                        {{-- Bagian Informasi Kamar --}}
                                        <div class="col-md-8 p-4 d-flex flex-column position-static">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <span class="badge mb-2" style="background-color: #8FB8E1">{{ $room->type->name }}</span>
                                                    <h5 class="mb-0 fw-bold" style="color:#50200C">{{ $room->number }} ~ {{ $room->name }}</h5>
                                                </div>
                                                <div class="text-end">
                                                    <small style="font-size: 0.75rem;" style="color:#50200C">Harga per Malam</small>
                                                    <h5 class="fw-bold mb-0" style="color:#50200C">{{ Helper::convertToRupiah($room->price) }}</h5>
                                                </div>
                                            </div>

                                            <div class="mb-3 d-flex gap-2 flex-wrap">
                                                <span class="badge bg-light border" style="color:#50200C">
                                                    <i class="fas fa-user me-1"></i> {{ $room->capacity }} Orang
                                                </span>
                                                @if($room->area_sqm)
                                                    <span class="badge bg-light border" style="color:#50200C">
                                                        <i class="fas fa-ruler-combined me-1"></i> {{ $room->area_sqm }} m²
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Fasilitas Kamar --}}
                                            <div class="mb-2">
                                                <small class="fw-bold text-uppercase" style="color:#50200C" style="font-size: 0.7rem;">
                                                    <i class="fas fa-tv me-1" style="color:#50200C"></i> Fasilitas Kamar
                                                </small>
                                                <p class="card-text small mb-0 text-truncate" style="color:#50200C">
                                                    {{ $room->room_facilities ?? 'Standar' }}
                                                </p>
                                            </div>

                                            {{-- Fasilitas Kamar Mandi (DITAMBAHKAN) --}}
                                            <div class="wrapper flex-grow-1 mb-3">
                                                <small class="fw-bold text-uppercase" style="color:#50200C" style="font-size: 0.7rem;">
                                                    <i class="fas fa-bath me-1" style="color:#50200C"></i> Kamar Mandi
                                                </small>
                                                <p class="card-text small mb-0 text-truncate" style="color:#50200C">
                                                    {{ $room->bathroom_facilities ?? 'Standar' }}
                                                </p>
                                            </div>

                                            <a href="{{ route('transaction.reservation.confirmation', ['customer' => $customer->id, 'room' => $room->id, 'from' => request()->input('check_in'), 'to' => request()->input('check_out'), 'count_person' => request()->input('count_person')]) }}"
                                                class="btn btn-choose w-100 mt-auto py-2 fw-bold shadow-sm">
                                                Pilih Kamar Ini <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                        
                                        {{-- Bagian Gambar Kamar --}}
                                        <div class="col-md-4 d-none d-md-block position-relative">
                                            {{-- Mengambil gambar utama (FIX: Pakai image_url) --}}
                                            <img src="{{ $room->image_url }}" 
                                                 class="img-fluid w-100 h-100" 
                                                 style="object-fit: cover; min-height: 280px;" 
                                                 alt="Gambar Kamar {{ $room->number }}">
                                                 
                                            {{-- Overlay gradient kecil supaya teks putih (jika ada) terbaca --}}
                                            <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-dark bg-opacity-50 text-white text-center d-md-none">
                                                <small>Lihat Detail</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="mb-3" style="color:#50200C">
                                        <i class="fas fa-search fa-3x opacity-50"></i>
                                    </div>
                                    <h5 style="color:#50200C">Tidak ada kamar tersedia.</h5>
                                    <p class="small" style="color:#50200C">Coba ubah filter tipe kamar atau tanggal pencarian Anda.</p>
                                </div>
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $rooms->onEachSide(1)->appends([
                                'count_person' => request()->input('count_person'),
                                'check_in' => request()->input('check_in'),
                                'check_out' => request()->input('check_out'),
                                'sort_price' => request()->input('sort_price'),
                                'type_id' => request()->input('type_id'),
                            ])->links('template.paginationlinks') }}
                        </div>
                        
                        <hr class="my-4">

                        {{-- Tombol Kembali --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('transaction.reservation.viewCountPerson', ['customer' => $customer->id]) }}" 
                            class="btn btn-modal-close me-3" 
                            id="btn-modal-close">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info Customer --}}
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1; background-color: #F7F3E4">
                    <div class="card-header border-0 pt-4 pb-0 text-center">
                        {{-- FIX: Pakai avatar_url --}}
                        <img src="{{ $customer->user->avatar_url }}"
                            class="rounded-circle shadow-sm border mb-3" 
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="fw-bold mb-0" style="color:#50200C">{{ $customer->name }}</h5>
                        <span class="badge mt-2" style="background-color: #C49A6C">{{ $customer->job }}</span>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-custom-list">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span class=" "><i class="fas fa-venus-mars me-2"></i> Jenis Kelamin</span>
                                <span class="fw-medium">
                                    <i class="fas {{ $customer->gender == 'Male' ? 'fa-male' : 'fa-female' }}"
                                    style="color: {{ $customer->gender == 'Male' ? '#A8D5BA' : '#F2C2B8' }};"></i>
                                    {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span class=" "><i class="fas fa-birthday-cake me-2"></i> Lahir</span>
                                <span class="fw-medium">{{ $customer->birthdate }}</span>
                            </li>
                            <li class="list-group-item px-0" style="color:#50200C">
                                <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Alamat</div>
                                <p class="mb-0 small fw-medium">{{ $customer->address }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
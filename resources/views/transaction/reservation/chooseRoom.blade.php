@extends('template.master')
@section('title', 'Pilih Kamar Reservasi')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        .wrapper { max-width: 400px; }
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
            background-color: #3d1809;
            color: #F7F3E4;
            transform: scale(1.01);
        }
    </style>
@endsection
@section('content')
    @include('transaction.reservation.progressbar')
    
    @php
        $types = \App\Models\Type::all();
        
        // 1. Tentukan status hari (Weekend/Weekday) berdasarkan Check-in
        $checkInDate = request()->input('check_in') ? \Carbon\Carbon::parse(request()->input('check_in')) : \Carbon\Carbon::now();
        $isWeekend = $checkInDate->isWeekend();
        $dayLabel = $isWeekend ? 'Weekend' : 'Weekday';

        // 2. Helper Hitung Harga
        function getDynamicPrice($room, $customerGroup, $isWeekend) {
            $specialPrice = \App\Models\TypePrice::where('type_id', $room->type_id)
                                ->where('customer_group', $customerGroup)
                                ->first();
            
            if ($specialPrice) {
                if ($isWeekend) {
                    return $specialPrice->price_weekend > 0 ? $specialPrice->price_weekend : $room->price;
                } else {
                    return $specialPrice->price_weekday > 0 ? $specialPrice->price_weekday : $room->price;
                }
            }
            return $room->price; // Harga default master room
        }
    @endphp

    <div class="container mt-4 mb-5" style="background-color: #F7F3E4;">
        <div class="row justify-content-center">
            {{-- Kolom Kiri: Daftar Kamar --}}
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        {{-- Header Informasi --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1" style="color:#50200C">
                                    <i class="fas fa-bed me-2" style="color:#50200C"></i> {{ $roomsCount }} Ruangan Tersedia
                                </h4>
                                <p class="mb-0 small" style="color:#50200C">
                                    <span class="badge bg-secondary me-1">{{ $dayLabel }}</span>
                                    <span class="badge bg-info text-dark me-2">{{ $customer->customer_group ?? 'General' }}</span>
                                    |
                                    {{ Helper::dateFormat(request()->input('check_in')) }} 
                                    <i class="fas fa-arrow-right mx-1"></i> 
                                    {{ Helper::dateFormat(request()->input('check_out')) }}
                                </p>
                            </div>
                        </div>
                        
                        <hr class="my-4" style="color:#50200C">
                        
                        {{-- Form Filter --}}
                        <form method="GET" action="{{ route('transaction.reservation.chooseRoom', ['customer' => $customer->id]) }}" class="mb-4">
                            <input type="hidden" name="count_person" value="{{ request()->input('count_person') }}">
                            <input type="hidden" name="check_in" value="{{ request()->input('check_in') }}">
                            <input type="hidden" name="check_out" value="{{ request()->input('check_out') }}">
                            
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label for="type_id" class="form-label small fw-bold text-uppercase" style="color:#50200C">Tipe Kamar</label>
                                    <select class="form-select shadow-sm" style="color:#50200C" id="type_id" name="type_id">
                                        <option value="" style="color:#50200C">Semua Tipe</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}" style="color:#50200C" @if(request()->input('type_id') == $type->id) selected @endif>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="sort_price" class="form-label small fw-bold text-uppercase" style="color:#50200C">Urutkan Harga</label>
                                    <select class="form-select shadow-sm" style="color:#50200C" id="sort_price" name="sort_price">
                                        <option value="ASC" style="color:#50200C" @if (request()->input('sort_price') == 'ASC') selected @endif>Termurah ke Termahal</option>
                                        <option value="DESC" style="color:#50200C" @if (request()->input('sort_price') == 'DESC') selected @endif>Termahal ke Termurah</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-choose w-100 shadow-sm">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Daftar Kamar --}}
                        <div class="d-grid gap-4">
                            @forelse ($rooms as $room)
                                @php
                                    // Hitung harga final berdasarkan hari & grup
                                    $finalPrice = getDynamicPrice($room, $customer->customer_group ?? 'General', $isWeekend);
                                @endphp

                                <div class="card card-room rounded overflow-hidden">
                                    <div class="row g-0">
                                        {{-- Info Kamar --}}
                                        <div class="col-md-8 p-4 d-flex flex-column position-static">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <span class="badge mb-2" style="background-color: #8FB8E1">{{ $room->type->name }}</span>
                                                    <h5 class="mb-0 fw-bold" style="color:#50200C">{{ $room->number }} ~ {{ $room->name }}</h5>
                                                </div>
                                                
                                                {{-- [UPDATE] Tampilan Harga Lebih Natural --}}
                                                <div class="text-end">
                                                    <small class="text-muted fw-bold" style="font-size: 0.75rem;">
                                                        Harga {{ $dayLabel }}
                                                    </small>
                                                    <h5 class="fw-bold mb-0" style="color:#50200C">
                                                        {{ Helper::convertToRupiah($finalPrice) }}
                                                    </h5>
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

                                            <div class="mb-2">
                                                <small class="fw-bold text-uppercase" style="color:#50200C; font-size: 0.7rem;">
                                                    <i class="fas fa-tv me-1"></i> Fasilitas Kamar
                                                </small>
                                                <p class="card-text small mb-0 text-truncate" style="color:#50200C">
                                                    {{ $room->room_facilities ?? 'Standar' }}
                                                </p>
                                            </div>

                                            <div class="wrapper flex-grow-1 mb-3">
                                                <small class="fw-bold text-uppercase" style="color:#50200C; font-size: 0.7rem;">
                                                    <i class="fas fa-bath me-1"></i> Kamar Mandi
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
                                        
                                        {{-- Gambar Kamar --}}
                                        <div class="col-md-4 d-none d-md-block position-relative">
                                            <img src="{{ $room->image_url }}" 
                                                 class="img-fluid w-100 h-100" 
                                                 style="object-fit: cover; min-height: 280px;" 
                                                 alt="Gambar Kamar {{ $room->number }}">
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
                            {{ $rooms->onEachSide(1)->appends(request()->except('page'))->links('template.paginationlinks') }}
                        </div>
                        
                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('transaction.reservation.viewCountPerson', ['customer' => $customer->id]) }}" 
                            class="btn btn-light border shadow-sm px-4" 
                            id="btn-modal-close" style="color:#50200C">
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
                        <img src="{{ $customer->user->avatar_url }}"
                            class="rounded-circle shadow-sm border mb-3" 
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="fw-bold mb-0" style="color:#50200C">{{ $customer->name }}</h5>
                        <p class="badge mt-2" style="background-color: #C49A6C">{{ $customer->job }}</p>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-custom-list">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span><i class="fas fa-users me-2"></i> Grup Tamu</span>
                                <span class="fw-bold text-primary">{{ $customer->customer_group ?? 'General' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span><i class="fas fa-venus-mars me-2"></i> Gender</span>
                                <span class="fw-medium">
                                    {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
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
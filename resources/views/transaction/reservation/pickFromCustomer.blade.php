@extends('template.master')
@section('title', 'Pilih Pelanggan')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        .myImages {
            transition: transform .3s;
        }
        .card:hover .myImages {
            transform: scale(1.03);
        }
        .customer-card {
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .customer-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            border-color: #C49A6C;
        }
        .badge-group {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .bg-general { background-color: #6c757d; color: white; }
        .bg-corporate { background-color: #0d6efd; color: white; }
        .bg-family { background-color: #198754; color: white; }
        .bg-government { background-color: #ffc107; color: black; }
    </style>
@endsection
@section('content')
    @include('transaction.reservation.progressbar')
    
    <div class="container mt-3" style="background-color: #F7F3E4; min-height: 100vh; padding-bottom: 50px;">
        
        {{-- Header & Search --}}
        <div class="row justify-content-center mt-4 mb-3">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #50200C">Pilih Tamu untuk Reservasi</h3>
                    <p class="text-muted">Cari tamu yang sudah terdaftar atau buat baru.</p>
                </div>
                
                <form class="d-flex shadow-sm rounded-pill overflow-hidden" method="GET" action="{{ route('transaction.reservation.pickFromCustomer') }}">
                    <input class="form-control border-0 py-3 ps-4" type="search" placeholder="Cari Nama / No. HP / Email..." aria-label="Search" 
                           name="q" value="{{ request()->input('q') }}">
                    <button class="btn px-4 fw-bold text-white" type="submit" style="background-color: #50200C;">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </form>

                {{-- Tombol Tambah Tamu Baru (Shortcut) --}}
                <div class="text-center mt-4">
                    <p class="text-muted mb-2">Belum terdaftar sebagai tamu?</p>
                    <a href="{{ route('transaction.reservation.createIdentity') }}" 
                    class="btn text-white fw-bold shadow-sm px-4 py-2" 
                    style="background-color: #50200C; border-radius: 50px; transition: transform 0.2s;">
                        <i class="fas fa-user-plus me-2"></i> Buat Tamu Baru
                    </a>
                </div>
            </div>
        </div>

        {{-- Hasil Pencarian --}}
        <div class="row justify-content-center mb-3">
            <div class="col-lg-12 text-center">
                @if (!empty(request()->input('q')))
                    <h5 style="color: #50200C">Hasil pencarian: "{{ request()->input('q') }}"</h5>
                    <p class="text-muted">Ditemukan {{ $customersCount }} data</p>
                @endif
            </div>
        </div>

        {{-- Pagination Atas --}}
        <div class="row justify-content-center mb-4">
            <div class="col-auto">
                <div class="pagination-block">
                    {{ $customers->onEachSide(1)->links('template.paginationlinks') }}
                </div>
            </div>
        </div>

        {{-- Grid Kartu Customer --}}
        <div class="row">
            @forelse ($customers as $customer)
                <div class="col-lg-3 col-md-4 col-sm-6 my-2">
                    <div class="card customer-card shadow-sm h-100 border-0" style="min-height:380px; position: relative;">
                        
                        {{-- [BARU] Badge Grup Tamu --}}
                        @php
                            $group = $customer->customer_group ?? 'WalkIn';
                            $badgeClass = 'bg-general';
                            if($group == 'OTA') $badgeClass = 'bg-corporate';
                            if($group == 'Corporate) $badgeClass = 'bg-family';
                            if($group == 'OwnerReferral') $badgeClass = 'bg-government';
                        @endphp
                        <span class="badge badge-group {{ $badgeClass }}">
                            {{ $group }}
                        </span>

                        {{-- Avatar --}}
                        <div class="overflow-hidden" style="height: 200px;">
                            @php
                                $avatar = method_exists($customer->user, 'getAvatar') ? $customer->user->getAvatar() : asset('img/default/default-user.jpg');
                            @endphp
                            <img class="myImages w-100 h-100" src="{{ $avatar }}" 
                                 style="object-fit: cover; border-top-right-radius: 0.5rem; border-top-left-radius: 0.5rem;"
                                 alt="{{ $customer->name }}">
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-truncate mb-1" style="color: #50200C" title="{{ $customer->name }}">
                                {{ $customer->name }}
                            </h5>
                            <p class="text-muted small mb-3">{{ $customer->job }}</p>

                            <div class="small flex-grow-1" style="color: #50200C">
                                <div class="mb-1 d-flex align-items-center">
                                    <i class="fas fa-envelope me-2 text-secondary" style="width: 20px"></i> 
                                    <span class="text-truncate">{{ $customer->user->email }}</span>
                                </div>
                                <div class="mb-1 d-flex align-items-center">
                                    <i class="fas fa-phone me-2 text-secondary" style="width: 20px"></i> 
                                    <span>{{ $customer->phone ?? '-' }}</span>
                                </div>
                                <div class="mb-1 d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-2 text-secondary" style="width: 20px"></i> 
                                    <span class="text-truncate">{{ Str::limit($customer->address, 30) }}</span>
                                </div>
                            </div>

                            <div class="d-grid mt-3">
                                <a href="{{ route('transaction.reservation.viewCountPerson', ['customer' => $customer->id]) }}"
                                   class="btn text-white fw-bold shadow-sm"
                                   style="background-color: #50200C;">
                                    Pilih Tamu <i class="fas fa-check ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted mb-3">
                        <i class="fas fa-users-slash fa-4x" style="color: #C49A6C"></i>
                    </div>
                    <h5 style="color: #50200C">Belum ada data tamu.</h5>
                    <p class="text-muted">Silakan tambah tamu baru untuk memulai reservasi.</p>
                    <a href="{{ route('transaction.reservation.createIdentity') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Tamu Baru
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination Bawah --}}
        <div class="row justify-content-center mt-4">
            <div class="col-auto">
                <div class="pagination-block">
                    {{ $customers->onEachSide(1)->links('template.paginationlinks') }}
                </div>
            </div>
        </div>
    </div>
@endsection
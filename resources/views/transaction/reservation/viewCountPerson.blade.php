@extends('template.master')
@section('title', 'Tentukan Jumlah Tamu')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        .btn-modal-close {
            background: linear-gradient(135deg, #F2C2B8 0%, #E8B3A8 100%);
            border: 1px solid #E8B3A8;
            border-radius: 0.75rem;
            color: #50200C;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .btn-modal-close:hover {
            background: linear-gradient(135deg, #E8B3A8 0%, #DDA498 100%);
            color: #50200C;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(80, 32, 12, 0.15);
        }
        .btn-modal-save {
            background-color: #50200C;
            color: white;
            border-radius: 0.75rem;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-modal-save:hover {
            background-color: #3d1809;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(80, 32, 12, 0.25);
        }
    </style>
@endsection

@section('content')
    @include('transaction.reservation.progressbar')
    
    <div class="container mt-3" style="min-height: 80vh;">
        <div class="row justify-content-md-center">
            
            {{-- Kolom Kiri: Form Input --}}
            <div class="col-md-8 mt-2">
                <div class="card shadow-sm border-0" style="border: 1px solid #e0e0e0;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="fw-bold" style="color: #50200C">Detail Reservasi</h4>
                        <p class="text-muted small">Tentukan tanggal menginap dan jumlah tamu.</p>
                    </div>
                    <div class="card-body p-4">
                        <form class="row g-3" method="GET" action="{{ route('transaction.reservation.chooseRoom', ['customer' => $customer->id]) }}">
                            
                            {{-- Jumlah Orang (Fixed 2) --}}
                            <div class="col-md-12">
                                <label for="count_person" class="form-label fw-bold" style="color:#50200C">
                                    <i class="fas fa-users me-1"></i> Jumlah Tamu (Maksimal)
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-center fw-bold" 
                                           style="color:#50200C; border-color: #C49A6C;" 
                                           id="count_person" name="count_person" value="2" readonly>
                                    <span class="input-group-text bg-white" style="color:#50200C; border-color: #C49A6C;">Orang</span>
                                </div>
                                <small class="text-muted">*Kapasitas standar kamar adalah 2 orang.</small>
                            </div>

                            {{-- Check In --}}
                            <div class="col-md-6 mt-4">
                                <label for="check_in" class="form-label fw-bold" style="color:#50200C">
                                    <i class="fas fa-calendar-check me-1"></i> Tanggal Check-In
                                </label>
                                <input type="date" class="form-control p-3 @error('check_in') is-invalid @enderror" 
                                       style="color:#50200C; border-color: #C49A6C;" 
                                       id="check_in" name="check_in" value="{{ old('check_in') }}" required>
                                @error('check_in')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Check Out --}}
                            <div class="col-md-6 mt-4">
                                <label for="check_out" class="form-label fw-bold" style="color:#50200C">
                                    <i class="fas fa-calendar-times me-1"></i> Tanggal Check-Out
                                </label>
                                <input type="date" class="form-control p-3 @error('check_out') is-invalid @enderror" 
                                       style="color:#50200C; border-color: #C49A6C;" 
                                       id="check_out" name="check_out" value="{{ old('check_out') }}" required>
                                @error('check_out')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="col-12 mt-5 pt-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    {{-- Tombol Kembali --}}
                                    <a href="{{ route('transaction.reservation.pickFromCustomer') }}" 
                                       class="btn btn-modal-close">
                                        <i class="fas fa-arrow-left me-2"></i> Ganti Tamu
                                    </a>

                                    {{-- Tombol Lanjut --}}
                                    <button type="submit" class="btn btn-modal-save shadow-sm">
                                        Pilih Kamar <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info Customer --}}
            <div class="col-md-4 mt-2">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1; background-color: #F7F3E4">
                    <div class="card-header border-0 pt-4 pb-0 text-center">
                        @php
                            $avatar = method_exists($customer->user, 'getAvatar') ? $customer->user->getAvatar() : asset('img/default/default-user.jpg');
                        @endphp
                        <img src="{{ $avatar }}"
                            class="rounded-circle shadow-sm border mb-3" 
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="fw-bold mb-0" style="color:#50200C">{{ $customer->name }}</h5>
                        <p class="badge mt-2" style="background-color: #C49A6C">{{ $customer->job }}</p>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush bg-custom-list">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span><i class="fas fa-users me-2"></i> Grup Tamu</span>
                                <span class="fw-bold text-primary">{{ $customer->customer_group ?? 'WalkIn' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span><i class="fas fa-venus-mars me-2"></i> Gender</span>
                                <span class="fw-medium">
                                    <i class="fas {{ $customer->gender == 'Male' ? 'fa-male' : 'fa-female' }}" 
                                       style="color: {{ $customer->gender == 'Male' ? '#A8D5BA' : '#F2C2B8' }};"></i>
                                    {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0" style="color:#50200C">
                                <span><i class="fas fa-birthday-cake me-2"></i> Lahir</span>
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
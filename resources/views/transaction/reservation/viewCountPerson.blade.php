@extends('template.master')
@section('title', 'Count Person')

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
            color: #8B4513;
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

        /* Style untuk tombol Kembali (Pink Gradasi) */
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
    </style>
@endsection

@section('content')
    @include('transaction.reservation.progressbar')
    
    <div class="container mt-3">
        <div class="row justify-content-md-center">
            
            {{-- Kolom Kiri: Form Input --}}
            <div class="col-md-8 mt-2">
                <div class="card shadow-sm border">
                    <div class="card-body p-3">
                        <div class="card">
                            <div class="card-body">
                                <form class="row g-3" method="GET" action="{{ route('transaction.reservation.chooseRoom', ['customer' => $customer->id]) }}">
                                    
                                    <div class="col-md-12">
                                        {{-- Jumlah Orang --}}
                                        <label for="count_person" class="form-label" style="color:#50200C">
                                            Berapa Banyak Orang?
                                        </label>
                                        <input type="text" class="form-control @error('count_person') is-invalid @enderror" 
                                            style="color:#50200C" 
                                            id="count_person" name="count_person" value="{{ old('count_person') }}">
                                        @error('count_person')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror

                                        {{-- Check In --}}
                                        <label for="check_in" class="form-label mt-3" style="color:#50200C">
                                            Dari
                                        </label>
                                        <input type="date" class="form-control @error('check_in') is-invalid @enderror" 
                                            style="color:#50200C" 
                                            id="check_in" name="check_in" value="{{ old('check_in') }}">
                                        @error('check_in')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror

                                        {{-- Check Out --}}
                                        <label for="check_out" class="form-label mt-3" style="color:#50200C">
                                            Sampai
                                        </label>
                                        <input type="date" class="form-control @error('check_out') is-invalid @enderror" 
                                            style="color:#50200C" 
                                            id="check_out" name="check_out" value="{{ old('check_out') }}">
                                        @error('check_out')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                                        {{-- Tombol Kembali --}}
                                        <a href="{{ route('transaction.reservation.createIdentity') }}" 
                                           class="btn btn-modal-close" 
                                           id="btn-modal-close">
                                            <i class="fas fa-arrow-left me-2"></i> Kembali
                                        </a>

                                        {{-- Tombol Lanjut --}}
                                        <button type="submit" class="btn btn-modal-save" id="btn-modal-save">
                                            Lanjut <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Info Customer --}}
            <div class="col-md-4 mt-2">
                <div class="card shadow-sm">
                    <img src="{{ $customer->user->getAvatar() }}"
                        style="border-top-right-radius: 0.5rem; border-top-left-radius: 0.5rem; width: 100%; object-fit: cover;">
                    <div class="card-body">
                        <table>
                            <tr>
                                <td style="text-align: center; width:50px">
                                    <span>
                                        <i class="fas {{ $customer->gender == 'Male' ? 'fa-male' : 'fa-female' }}" 
                                           style="color: {{ $customer->gender == 'Male' ? '#A8D5BA' : '#F2C2B8' }};">
                                        </i>
                                    </span>
                                </td>
                                <td>
                                    {{ $customer->name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <span><i class="fas fa-user-md"></i></span>
                                </td>
                                <td>{{ $customer->job }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <span><i class="fas fa-birthday-cake"></i></span>
                                </td>
                                <td>{{ $customer->birthdate }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">
                                    <span><i class="fas fa-map-marker-alt"></i></span>
                                </td>
                                <td>{{ $customer->address }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
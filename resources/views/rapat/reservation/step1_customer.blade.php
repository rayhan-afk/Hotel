@extends('template.master')
@section('title', 'Reservasi Ruang Rapat - Data Pemesan')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
@endsection

@section('content')
    <div class="container mt-3">
        @include('rapat.reservation._progressbar') <div class="row justify-content-md-center mt-4">
            <div class="col-lg-10"> <div class="card shadow-sm border">
                    <div class="card-body p-3">
                        <h2 style="color:#50200C">Reservasi Ruang Rapat</h2>
                        <form class="row g-3" method="POST" action="{{ route('rapat.reservation.storeStep1') }}">
                            @csrf
                            <div class="col-md-6">
                                <label for="nama" class="form-label" style="color:#50200C">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                    name="nama" value="{{ old('nama', $customer['nama'] ?? '') }}" required>
                                @error('nama')
                                    <div class="text-danger mt-1" style="color:#50200C">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label" style="color:#50200C">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" value="{{ old('email', $customer['email'] ?? '') }}" required>
                                @error('email')
                                    <div class="text-danger mt-1" style="color:#50200C">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label" style="color:#50200C">Nomor HP <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp"
                                    name="no_hp" value="{{ old('no_hp', $customer['no_hp'] ?? '') }}" required>
                                @error('no_hp')
                                    <div class="text-danger mt-1" style="color:#50200C">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="instansi" class="form-label" style="color:#50200C">Instansi/Perusahaan</label>
                                <input type="text" class="form-control @error('instansi') is-invalid @enderror" id="instansi"
                                    name="instansi" value="{{ old('instansi', $customer['instansi'] ?? '') }}">
                                @error('instansi')
                                    <div class="text-danger mt-1" style="color:#50200C">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 d-flex justify-content-end">
                                <a href="{{ route('rapat.reservation.cancel') }}" class="btn btn-modal-close me-3" id="btn-modal-close">Batal</a>
                                <button type="submit" class="btn btn-modal-save shadow-sm" id="btn-modal-save">Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('template.master')
@section('title', 'Pilih Waktu Reservasi')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
@endsection

@section('content')
    <div class="container mt-3">
        @include('rapat.reservation._progressbar') 
        
        <div class="row justify-content-md-center mt-4">
            <div class="col-md-8 mt-2">
                <div class="card shadow-sm border">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('rapat.reservation.storeStep2') }}">
                            @csrf
                            
                            {{-- Tanggal Pemakaian --}}
                            <div class="mb-3">
                                <label for="tanggal_pemakaian" class="form-label fw-bold" style="color:#50200C">Tanggal Pemakaian</label>
                                <input type="date" class="form-control @error('tanggal_pemakaian') is-invalid @enderror" 
                                       style="color:#50200C" id="tanggal_pemakaian" name="tanggal_pemakaian" 
                                       value="{{ old('tanggal_pemakaian', $timeInfo['tanggal_pemakaian'] ?? '') }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                @error('tanggal_pemakaian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                {{-- Waktu Mulai (Jadwal) --}}
                                <div class="col-md-4 mb-3">
                                    <label for="waktu_mulai" class="form-label fw-bold" style="color:#50200C">Waktu Mulai</label>
                                    <input type="time" class="form-control @error('waktu_mulai') is-invalid @enderror" 
                                           style="color:#50200C" id="waktu_mulai" name="waktu_mulai" 
                                           value="{{ old('waktu_mulai', $timeInfo['waktu_mulai'] ?? '') }}" required>
                                    @error('waktu_mulai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Waktu Selesai (Jadwal) --}}
                                <div class="col-md-4 mb-3">
                                    <label for="waktu_selesai" class="form-label fw-bold" style="color:#50200C">Waktu Selesai</label>
                                    <input type="time" class="form-control @error('waktu_selesai') is-invalid @enderror" 
                                           style="color:#50200C" id="waktu_selesai" name="waktu_selesai" 
                                           value="{{ old('waktu_selesai', $timeInfo['waktu_selesai'] ?? '') }}" required>
                                    @error('waktu_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- INPUT BARU: Durasi (Jam) untuk Perhitungan Biaya --}}
                                <div class="col-md-4 mb-3">
                                    <label for="durasi_jam" class="form-label fw-bold" style="color:#50200C">Durasi Bayar (Jam)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('durasi_jam') is-invalid @enderror" 
                                               style="color:#50200C" id="durasi_jam" name="durasi_jam" 
                                               value="{{ old('durasi_jam', $timeInfo['durasi_jam'] ?? '1') }}" 
                                               min="1" max="24" placeholder="Contoh: 3" required>
                                        <span class="input-group-text" style="color:#50200C">Jam</span>
                                    </div>
                                    <div class="form-text text-muted small" style="color:#50200C">
                                        *Biaya sewa ruang dihitung berdasarkan input ini (Rp 100rb/jam).
                                    </div>
                                    @error('durasi_jam')
                                        <div class="invalid-feedback" style="color:#50200C">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Info Customer (Readonly) --}}
                            <div class="alert alert-light border mt-2" style="color:#50200C">
                                <small class="text-muted d-block mb-1">Pemesan:</small>
                                <strong>{{ $customer['nama'] }}</strong> ({{ $customer['instansi'] ?? 'Perorangan' }})
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('rapat.reservation.showStep1') }}" class="btn btn-modal-close" id="btn-modal-close">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-modal-save" id="btn-modal-save">
                                    Lanjut <i class="fas fa-arrow-right me-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
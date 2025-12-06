@extends('template.master')
@section('title', 'Reservasi Ruang Rapat - Pilih Paket')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        /* Tiru style dari chooseRoom.blade.php */
        .wrapper { max-width: 400px; }
        .demo-1 {
            overflow: hidden; display: -webkit-box;
            -webkit-line-clamp: 2; /* 2 baris saja */
            -webkit-box-orient: vertical;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-3">
        @include('rapat.reservation._progressbar') 
        
        <div class="row justify-content-md-center mt-4">
            
            <div class="col-md-8 mt-2">
                <div class="card shadow-sm border">
                    <div class="card-body p-3" style="color:#50200C">
                        
                        <h2>{{ $paketsCount }} Paket Tersedia untuk:</h2>
                        <p>
                            Tanggal: <strong>{{ Helper::dateFormat($timeInfo['tanggal_pemakaian']) }}</strong> | 
                            Waktu: <strong>{{ $timeInfo['waktu_mulai'] }} - {{ $timeInfo['waktu_selesai'] }}</strong>
                        </p>
                        <hr>
                        
                        <form action="{{ route('rapat.reservation.storeStep3') }}" method="POST">
                            @csrf
                            
                            <div class="col-md-12 mb-3">
                                <label for="jumlah_peserta" class="form-label">Jumlah Peserta <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('jumlah_peserta') is-invalid @enderror" 
                                    id="jumlah_peserta" name="jumlah_peserta" value="{{ old('jumlah_peserta', $selectedPaket['jumlah_peserta'] ?? 1) }}" min="1">
                                @error('jumlah_peserta')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <label class="form-label">Pilih Paket <span class="text-danger">*</span></label>
                                @forelse ($pakets as $paket)
                                    <div class="col-lg-12">
                                        <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                                            <div class="col p-4 d-flex flex-column position-static">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="ruang_rapat_paket_id" id="paket-{{ $paket->id }}" value="{{ $paket->id }}" 
                                                        {{ (old('ruang_rapat_paket_id', $selectedPaket['ruang_rapat_paket_id'] ?? null) == $paket->id) ? 'checked' : '' }} required>
                                                    
                                                    <label class="form-check-label w-100" for="paket-{{ $paket->id }}">
                                                        <h4 class="mb-0">{{ $paket->name }}</h4>
                                                    </label>
                                                </div>
                                                <div class="mb-1 text-muted">{{ Helper::convertToRupiah($paket->harga) }} / Jam</div>
                                                <div class="wrapper">
                                                    <p class="card-text mb-auto demo-1"><strong>Isi Paket:</strong> {{ $paket->isi_paket }}</p>
                                                    <p class="card-text mb-auto demo-1"><strong>Fasilitas:</strong> {{ $paket->fasilitas }}</p>
                                                </div>
                                            </div>
                                            <div class="col-auto d-none d-lg-block">
                                                <svg class="bd-placeholder-img" width="200" height="200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Thumbnail" preserveAspectRatio="xMidYMid slice" focusable="false"><title>Placeholder</title><rect width="100%" height="100%" fill="#55595c"></rect><text x="50%" y="50%" fill="#eceeef" dy=".3em">Image</text></svg>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <h4 class="text-center text-muted col-12">Tidak ada paket tersedia.</h4>
                                @endforelse
                                @error('ruang_rapat_paket_id')
                                    <div class="text-danger mt-1 col-12">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('rapat.reservation.showStep2') }}" class="btn btn-modal-close" id="btn-modal-close"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                                @if(count($pakets) > 0)
                                    <button type="submit" class="btn btn-modal-save" id="btn-modal-save">Lanjut ke Konfirmasi <i class="fas fa-arrow-right ms-1"></i></button>
                                @endif
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-lg-12 mt-3">
                                {{ $pakets->onEachSide(1)->links('template.paginationlinks') }}
                                </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mt-2">
                <div class="card shadow-sm">
                    <img src="{{ asset('img/default/default-user.jpg') }}"
                        style="border-top-right-radius: 0.5rem; border-top-left-radius: 0.5rem; object-fit: cover; height: 200px;">
                    <div class="card-body">
                        <h5>Info Pemesanan</h5>
                        <table>
                            <tr>
                                <td style="text-align: center; width:50px"><i class="fas fa-user"></i></td>
                                <td>{{ $customer['nama'] }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;"><i class="fas fa-envelope"></i></td>
                                <td>{{ $customer['email'] }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;"><i class="fas fa-phone"></i></td>
                                <td>{{ $customer['no_hp'] }}</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;"><i class="fas fa-building"></i></td>
                                <td>{{ $customer['instansi'] ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            </div>
    </div>
@endsection
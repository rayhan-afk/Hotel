@extends('template.master')
@section('title', 'Konfirmasi Reservasi')
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
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2" style="color:#50200C"></i>Langkah 4: Konfirmasi & Pembayaran</h5>
                        
                        {{-- INFO PAKET --}}
                        <div class="alert alert-light border">
                            <div class="row">
                                <div class="col-md-6" style="color:#50200C">
                                    <strong>Paket:</strong> {{ $paket->name }}
                                </div>
                                <div class="col-md-6 text-end" style="color:#50200C">
                                    <strong>Peserta:</strong> {{ $jumlahOrang }} Orang
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- RINCIAN BIAYA --}}
                        <h6 class="fw-bold mb-3" style="color:#50200C">Rincian Biaya</h6>
                        <table class="table table-sm bg-light rounded border" style="color:#50200C">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Deskripsi</th>
                                    <th class="text-end">Perhitungan</th>
                                    <th class="text-end pe-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 1. BIAYA PAKET --}}
                                <tr>
                                    <td class="ps-3" style="color:#50200C">Biaya Paket (Per Orang)</td>
                                    <td class="text-end" style="color:#50200C">{{ Helper::convertToRupiah($paket->harga) }} x {{ $jumlahOrang }} org</td>
                                    <td class="text-end pe-3 fw-bold" style="color:#50200C">{{ Helper::convertToRupiah($biayaPaketTotal) }}</td>
                                </tr>
                                
                                {{-- 2. BIAYA SEWA RUANG (DURASI) --}}
                                <tr>
                                    <td class="ps-3" style="color:#50200C">Sewa Ruang ({{ $durasiJam }} Jam)</td>
                                    <td class="text-end" style="color:#50200C">Rp 100.000 x {{ $durasiJam }} jam</td>
                                    <td class="text-end pe-3 fw-bold" style="color:#50200C">{{ Helper::convertToRupiah($biayaSewaRuangTotal) }}</td>
                                </tr>

                                {{-- 3. PAJAK 10% (BARU) --}}
                                <tr style="border-top: 1px dashed #ccc;">
                                    <td class="ps-3 fw-bold" style="color:#50200C">Pajak PB1 (10%)</td>
                                    <td class="text-end small text-muted" style="color:#50200C">
                                        {{ Helper::convertToRupiah($subTotal) }} x 10%
                                    </td>
                                    <td class="text-end pe-3 fw-bold" style="color:#50200C">
                                        {{ Helper::convertToRupiah($pajak) }}
                                    </td>
                                </tr>
                                
                                {{-- TOTAL TAGIHAN (GRAND TOTAL) --}}
                                <tr class="border-top border-secondary bg-white">
                                    <td class="ps-3 pt-3 fs-5 fw-bold" style="color:#50200C">TOTAL TAGIHAN</td>
                                    <td></td>
                                    <td class="text-end pe-3 pt-3 fs-4 fw-bold" style="color:#50200C">
                                        {{ Helper::convertToRupiah($totalHarga) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- TOMBOL KONFIRMASI --}}
                        <form method="POST" action="{{ route('rapat.reservation.processPayment') }}" class="mt-4">
                            @csrf
                            
                            <div class="alert d-flex align-items-center" role="alert" style="color:#50200C">
                                <i class="fas fa-check-circle fs-4 me-3" style="color:#50200C"></i>
                                <div>
                                    Klik tombol di bawah untuk konfirmasi. Transaksi akan otomatis dicatat <strong>Lunas (Paid)</strong>.
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('rapat.reservation.showStep3') }}" class="btn btn-modal-close" id="btn-modal-close">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-modal-save" id="btn-modal-save">
                                    <i class="fas fa-save me-2" style="color:#50200C"></i> Bayar & Simpan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            {{-- SIDEBAR INFO PEMESAN & JADWAL --}}
            <div class="col-md-4 mt-2">
                <div class="card shadow-sm">
                    <div class="card-body" style="color:#50200C">
                        <h5 class="card-title fw-bold mb-3" style="color:#50200C">Info Pemesan</h5>
                        <p class="mb-1"><i class="fas fa-user me-2 text-muted" style="color:#50200C"></i> {{ $customer['nama'] }}</p>
                        <p class="mb-1"><i class="fas fa-building me-2 text-muted" style="color:#50200C"></i> {{ $customer['instansi'] ?? '-' }}</p>
                        <hr>
                        <h5 class="card-title fw-bold mb-3" style="color:#50200C">Waktu</h5>
                        <p class="mb-1"><i class="fas fa-calendar me-2 text-muted" style="color:#50200C"></i> {{ Helper::dateFormat($timeInfo['tanggal_pemakaian']) }}</p>
                        <p class="mb-1"><i class="fas fa-clock me-2 text-muted" style="color:#50200C"></i> {{ $timeInfo['waktu_mulai'] }} - {{ $timeInfo['waktu_selesai'] }}</p>
                        <p class="mb-0 fw-bold"><i class="fas fa-hourglass-half me-2" style="color:#50200C"></i> Durasi Bayar: {{ $durasiJam }} Jam</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
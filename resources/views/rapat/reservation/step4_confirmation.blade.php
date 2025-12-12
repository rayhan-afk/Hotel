@extends('template.master')
@section('title', 'Konfirmasi Reservasi')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        /* CSS CUSTOM TOMBOL DOWNLOAD INVOICE */
        .btn-download-invoice {
            /* Gradasi: Sedikit lebih terang di awal, berakhir di #8FB8E1 */
            background: linear-gradient(135deg, #A8C9E8 0%, #8FB8E1 100%) !important;
            border: 1px solid #8FB8E1 !important;
            border-radius: 0.75rem !important;
            
            /* Warna Teks Putih */
            color: #50200C !important;
            
            /* Layout & Typography */
            padding: 0.75rem 1.2rem !important;
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            line-height: 1.5rem !important;
            
            box-sizing: border-box !important;
            cursor: pointer !important;
            flex: 0 0 auto !important;
            text-align: center !important;
            text-decoration: none !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            touch-action: manipulation !important;
            width: auto !important;
            
            /* Shadow dengan nuansa #8FB8E1 */
            box-shadow: 0 4px 12px rgba(143, 184, 225, 0.3) !important;
            transition-duration: 0.3s !important;
            transition-property: background-color, border-color, color, fill, stroke, box-shadow, transform !important;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Efek Hover */
        .btn-download-invoice:hover {
            background: linear-gradient(135deg, #8FB8E1 0%, #7DA3CC 100%) !important;
            border-color: #7DA3CC !important;
            box-shadow: 0 8px 20px rgba(143, 184, 225, 0.45) !important;
            transform: translateY(-2px) !important;
            color: #50200C !important;
        }

        /* Efek Klik */
        .btn-download-invoice:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 8px rgba(143, 184, 225, 0.25) !important;
        }

        /* Efek Fokus */
        .btn-download-invoice:focus {
            box-shadow: 0 0 0 3px rgba(143, 184, 225, 0.5), 0 4px 12px rgba(143, 184, 225, 0.25) !important;
            outline: 2px solid transparent !important;
            outline-offset: 2px !important;
        }

        @media (min-width: 768px) {
            .btn-download-invoice {
                padding: 0.75rem 1.5rem !important;
            }
        }
    </style>
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
                        <div class="alert alert-light border mt-3">
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

                                {{-- 3. PAJAK 10% --}}
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
                            
                            <div class="d-flex justify-content-between align-items-center">
                                {{-- KIRI: Tombol Kembali --}}
                                <a href="{{ route('rapat.reservation.showStep3') }}" class="btn btn-modal-close" id="btn-modal-close">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>

                                {{-- KANAN: Group Tombol Aksi --}}
                                <div class="d-flex gap-2">
                                    {{-- [UPDATE] Menggunakan class 'btn-download-invoice' --}}
                                    <a href="{{ route('rapat.reservation.previewInvoice') }}" target="_blank" class="btn-download-invoice me-2">
                                        <i class="fas fa-file-download me-1"></i> Download Invoice
                                    </a>

                                    <button type="submit" class="btn btn-modal-save" id="btn-modal-save">
                                        <i class="fas fa-save me-2" style="color:#50200C"></i> Bayar & Simpan
                                    </button>
                                </div>
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
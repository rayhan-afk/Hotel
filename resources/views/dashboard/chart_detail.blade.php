@extends('template.master')
@section('title', 'Detail Data Tamu')

@section('content')
    <div class="container-fluid mt-3">
        {{-- Header Judul --}}
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="text-center">
                    <h3 class="fw-bold" style="color: #50200C">
                        <i class="fas fa-calendar-alt me-2"></i>Data Tamu Tanggal: {{ Helper::dateFormat($date) }}
                    </h3>
                    <p class="text-muted">Menampilkan daftar Reservasi, Check In, dan Tamu yang sudah Check Out.</p>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse ($transactions as $transaction)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 overflow-hidden">
                        <div class="row g-0 h-100">
                            {{-- Kolom Kiri: Info Utama --}}
                            <div class="col-8 p-3 d-flex flex-column position-static">
                                
                                {{-- Nomor Kamar Badge --}}
                                <div class="mb-2">
                                    <a href="{{ route('room.show', ['room' => $transaction->room->id]) }}" 
                                       class="badge text-decoration-none" 
                                       style="background-color: #50200C; font-size: 0.9rem;">
                                        Kamar {{ $transaction->room->number }}
                                    </a>
                                    
                                    {{-- Status Transaksi Badge --}}
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        $statusLabel = $transaction->status;

                                        if($transaction->status == 'Check In') {
                                            $badgeClass = 'bg-success';
                                        } elseif($transaction->status == 'Reservation') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif($transaction->status == 'Payment Success') {
                                            $badgeClass = 'bg-primary'; // Biru untuk yang sudah lunas/checkout
                                            $statusLabel = 'Check Out / Lunas';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} ms-1">{{ $statusLabel }}</span>
                                </div>

                                {{-- Nama Tamu --}}
                                <h5 class="mb-1 fw-bold">
                                    <a href="{{ route('customer.show', ['customer' => $transaction->customer->id]) }}" 
                                       class="text-decoration-none text-dark">
                                        {{ $transaction->customer->name }}
                                    </a>
                                </h5>

                                {{-- Durasi Menginap --}}
                                <div class="mb-3 text-muted small">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ Helper::dateFormat($transaction->check_in) }} 
                                    <i class="fas fa-arrow-right mx-1"></i> 
                                    {{ Helper::dateFormat($transaction->check_out) }}
                                </div>

                                {{-- Status Kamar (Fisik) --}}
                                <p class="card-text mt-auto small text-muted">
                                    Status Kamar Saat Ini: <br>
                                    <span class="fw-bold text-dark">
                                        {{ $transaction->room->roomStatus->name ?? '-' }}
                                    </span>
                                </p>
                            </div>

                            {{-- Kolom Kanan: Foto (Hanya di layar besar) --}}
                            <div class="col-4 d-none d-sm-block">
                                <img src="{{ $transaction->customer->user->getAvatar() }}" 
                                     class="img-fluid h-100 w-100" 
                                     style="object-fit: cover;" 
                                     alt="Foto Tamu">
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Tampilan Jika Kosong --}}
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="mb-3" style="color: #e0e0e0;">
                            <i class="fas fa-folder-open fa-4x"></i>
                        </div>
                        <h5 class="text-muted">Tidak ada data tamu pada tanggal ini.</h5>
                    </div>
                </div>
            @endforelse
        </div>
        
        {{-- Tombol Kembali --}}
        <div class="row mt-3">
            <div class="col-12 text-center">
                <a href="{{ route('dashboard.index') }}" class="btn btn-modal-close">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
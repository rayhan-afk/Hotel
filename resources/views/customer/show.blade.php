@extends('template.master')
@section('title', 'Detail Customer')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        {{-- BAGIAN KIRI: PROFIL CUSTOMER --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border mb-4" style="border-color: #C49A6C;">
                <div class="card-header py-3" style="background-color: #F7F3E4; color: #50200C; border-bottom: 1px solid #C49A6C;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i>Profil Tamu</h5>
                </div>
                <div class="card-body text-center p-4" style="background-color: #fff; color: #50200C;">
                    {{-- Avatar --}}
                    <div class="mb-3">
                        <img src="{{ $customer->user->getAvatar() }}" 
                             class="rounded-circle border shadow-sm p-1" 
                             style="width: 120px; height: 120px; object-fit: cover; border-color: #C49A6C !important;" 
                             alt="{{ $customer->name }}">
                    </div>
                    
                    <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
                    <span class="badge mb-3" style="background-color: #50200C;">{{ $customer->job }}</span>

                    <hr style="border-color: #C49A6C;">

                    {{-- Detail Kontak --}}
                    <div class="text-start">
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2 text-muted"></i> {{ $customer->user->email }}
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i> {{ $customer->phone ?? '-' }}
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-venus-mars me-2 text-muted"></i> 
                            {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i> {{ $customer->address }}
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('customer.index') }}" class="btn btn-sm w-100 shadow-sm" 
                           style="background-color: #50200C; color: #F7F3E4; border: 1px solid #C49A6C; ">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN: RIWAYAT RESERVASI --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border" style="border-color: #C49A6C;">
                <div class="card-header py-3" style="background-color: #50200C; color: #F7F3E4;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Riwayat Menginap</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #F7F3E4; color: #50200C;">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="py-3">Kamar</th>
                                    <th class="py-3">Check-In</th>
                                    <th class="py-3">Check-Out</th>
                                    <th class="py-3">Durasi</th>
                                    <th class="py-3 text-end px-4">Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Asumsi relasi di model Customer adalah 'transactions' --}}
                                @forelse ($customer->transactions as $transaction)
                                    <tr>
                                        <td class="px-4 fw-bold text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="d-block fw-bold text-dark">
                                                {{ $transaction->room->number ?? '-' }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $transaction->room->type->name ?? 'Tipe Dihapus' }}
                                            </small>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->check_in)->format('d M Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->check_out)->format('d M Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($transaction->check_in)->diffInDays(\Carbon\Carbon::parse($transaction->check_out)) }} Malam
                                        </td>
                                        <td class="text-end px-4 fw-bold" style="color: #50200C;">
                                            Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-ghost fa-2x mb-2"></i>
                                            <p class="mb-0">Belum ada riwayat reservasi.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
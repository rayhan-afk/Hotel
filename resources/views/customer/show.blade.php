@extends('template.master')
@section('title', 'Detail Customer')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        {{-- BAGIAN KIRI: PROFIL CUSTOMER (TETAP SAMA) --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border mb-4" style="border-color: #C49A6C;">
                <div class="card-header py-3" style="background-color: #F7F3E4; color: #50200C; border-bottom: 1px solid #C49A6C;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i>Profil Tamu</h5>
                </div>
                <div class="card-body text-center p-4" style="background-color: #fff; color: #50200C;">
                    <div class="mb-3">
                        <img src="{{ $customer->user->getAvatar() }}" 
                             class="rounded-3 border shadow-sm p-1" 
                             style="width: 180px; aspect-ratio: 16 / 9; object-fit: cover; border-color: #C49A6C !important;" 
                             alt="{{ $customer->name }}">
                    </div>
                    <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
                    <div class="mb-3">
                        <span class="badge me-1" style="background-color: #50200C;">{{ $customer->job }}</span>
                        @php
                            $group = $customer->customer_group ?? 'WalkIn';
                            $badgeClass = 'bg-secondary';
                            if($group == 'OTA') $badgeClass = 'bg-primary';
                            if($group == 'Corporate') $badgeClass = 'bg-success';
                            if($group == 'OwnerReferral') $badgeClass = 'bg-warning text-dark';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $group }}</span>
                    </div>
                    <hr style="border-color: #C49A6C;">
                    <div class="text-start" style="color: #50200C">
                        <div class="mb-2"><i class="fas fa-envelope me-2" style="width: 20px; text-align: center;"></i> {{ $customer->user->email }}</div>
                        <div class="mb-2"><i class="fas fa-phone me-2" style="width: 20px; text-align: center;"></i> {{ $customer->phone ?? '-' }}</div>
                        <div class="mb-2"><i class="fas fa-venus-mars me-2" style="width: 20px; text-align: center;"></i> {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}</div>
                        <div class="mb-2 d-flex"><i class="fas fa-map-marker-alt me-2 mt-1" style="width: 20px; text-align: center;"></i> <span>{{ $customer->address }}</span></div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('customer.index') }}" class="btn btn-sm w-100 shadow-sm" style="background-color: #50200C; color: #F7F3E4; border: 1px solid #C49A6C;">
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
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Riwayat Reservasi & Transaksi</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #F7F3E4; color: #50200C;">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="py-3">Kamar</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3">Check-In</th>
                                    <th class="py-3">Check-Out</th>
                                    <th class="py-3 text-end px-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customer->transactions as $transaction)
                                    <tr>
                                        <td class="px-4 fw-bold" style="color: #50200C">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="d-block fw-bold" style="color: #50200C">{{ $transaction->room->number ?? '-' }}</span>
                                            <small class="text-muted">{{ $transaction->room->type->name ?? 'Tipe Dihapus' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $status = $transaction->status;
                                                $badgeColor = match($status) {
                                                    'Canceled'  => 'bg-danger', // Default bootstrap (kadang pink)
                                                    'Check In'  => 'bg-success',
                                                    'Check Out' => 'bg-secondary',
                                                    'Booking'   => 'bg-warning text-dark',
                                                    'Payment Pending' => 'bg-info text-dark',
                                                    default     => 'bg-light text-dark border'
                                                };
                                            @endphp

                                            {{-- JIKA CANCELED: GUNAKAN WARNA MERAH KHUSUS --}}
                                            @if($status == 'Canceled')
                                                <button type="button" 
                                                        class="btn badge shadow-sm border-0 btn-view-cancel-reason"
                                                        {{-- Style khusus biar MERAH MATANG (Bukan Pink) --}}
                                                        style="background-color: #D32F2F !important; color: white;"
                                                        data-reason="{{ $transaction->cancel_reason ?? 'Tidak ada alasan' }}"
                                                        data-notes="{{ $transaction->cancel_notes ?? '-' }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalCancelDetail"
                                                        title="Klik untuk lihat alasan">
                                                    {{ $status }} <i class="fas fa-search-plus ms-1" style="font-size: 0.7em;"></i>
                                                </button>
                                                
                                                {{-- Teks Alasan di Bawahnya --}}
                                                <div class="mt-1 small fw-bold" style="font-size: 0.7rem; color: #D32F2F;">
                                                    {{ $transaction->cancel_reason ?? '' }}
                                                </div>
                                            @else
                                                <span class="badge {{ $badgeColor }} shadow-sm">{{ $status }}</span>
                                            @endif
                                        </td>
                                        <td style="color: #50200C">{{ \Carbon\Carbon::parse($transaction->check_in)->format('d M Y') }}</td>
                                        <td style="color: #50200C">{{ \Carbon\Carbon::parse($transaction->check_out)->format('d M Y') }}</td>
                                        <td class="text-end px-4 fw-bold" style="color: #50200C;">
                                            @if($status == 'Canceled')
                                                <span class="text-decoration-line-through text-muted small me-1">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span><br><span class="badge bg-danger" style="background-color: #D32F2F !important;">0</span>
                                            @else
                                                Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5" style="color: #50200C">
                                            <i class="fas fa-ghost fa-2x mb-2 d-block opacity-50"></i>
                                            <p class="mb-0">Belum ada riwayat transaksi.</p>
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

{{-- MODAL DETAIL CANCEL --}}
<div class="modal fade" id="modalCancelDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            {{-- Header Modal juga Merah --}}
            <div class="modal-header text-white" style="background-color: #D32F2F;">
                <h6 class="modal-title fw-bold"><i class="fas fa-info-circle me-1"></i> Detail Pembatalan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Alasan Utama</small>
                    <h5 class="fw-bold mt-1" style="color: #D32F2F;" id="modalCancelReason">...</h5>
                </div>
                <hr class="w-50 mx-auto text-muted">
                <div>
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Catatan Tambahan</small>
                    <p class="mb-0 mt-1 text-dark fst-italic" id="modalCancelNotes">...</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center bg-light p-2">
                <button type="button" class="btn btn-sm btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalCancelDetail');
        const reasonText = document.getElementById('modalCancelReason');
        const notesText = document.getElementById('modalCancelNotes');

        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; 
            const reason = button.getAttribute('data-reason');
            const notes = button.getAttribute('data-notes');
            reasonText.textContent = reason;
            notesText.textContent = notes && notes !== '-' ? notes : 'Tidak ada catatan tambahan.';
        });
    });
</script>
@endsection
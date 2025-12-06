@extends('template.master')
@section('title', 'Dashboard')
@section('content')
    <div id="dashboard" class="fade-in" style="background-color: #F7F3E4;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 text-gradient mb-1">Selamat Datang {{ auth()->user()->role }}, {{ auth()->user()->name }}!</h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- Stats Cards -->
        <div class="row mb-4">
            
            {{-- 1. KAMAR TERSEDIA (Available Rooms) --}}
            {{-- Logika Baru: Total - (Terpakai + Kotor) --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('room-info.available') }}" class="card-link text-decoration-none h-100">
                    <div class="card card-stats h-100">
                        <div class="card-body text-center">
                            <div class="stats-number">{{ $availableRoomsCount }}</div>
                            <div class="stats-label">
                                <i class="fas fa-bed me-2"></i>
                                Kamar Tersedia
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            {{-- 2. KAMAR TERPAKAI (Occupied Rooms) --}}
            {{-- Link: Diarahkan ke Check-In (karena daftar tamu aktif & tombol Check Out ada disana) --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('transaction.checkin.index') }}" class="card-link text-decoration-none h-100"> 
                    <div class="card card-stats card-stats-success h-100">
                        <div class="card-body text-center">
                            <div class="stats-number">
                                {{ $occupiedRoomsCount }}
                            </div>
                            <div class="stats-label">
                                <i class="fas fa-check-circle me-2"></i> 
                                Kamar Terpakai
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            {{-- 3. RESERVASI HARI INI (Reservations) --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('room-info.reservation') }}" class="card-link text-decoration-none h-100">
                    <div class="card card-stats card-stats-warning h-100">
                        <div class="card-body text-center">
                            <div class="stats-number">
                                {{ $todayReservationsCount }}
                            </div>
                            <div class="stats-label">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Jumlah Reservasi
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            {{-- 4. KAMAR DIBERSIHKAN (Cleaning Rooms) --}}
            {{-- Link: Diarahkan ke halaman Cleaning agar bisa klik tombol 'Selesai' --}}
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('room-info.cleaning') }}" class="card-link text-decoration-none h-100">
                    <div class="card card-stats card-stats-danger h-100">
                        <div class="card-body text-center">
                            <div class="stats-number">
                                {{ $cleaningRoomsCount }}
                            </div>
                            <div class="stats-label">
                                <i class="fas fa-broom me-2"></i>
                                Sedang Dibersihkan
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">

    <!-- ===================== TAMU HARI INI (KIRI) ===================== -->
    <div class="col-lg-8 mb-4">
        <div class="card card-lh h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold" style="color:#50200C">
                        <i class="fas fa-calendar-day me-2"></i>
                        Tamu Hari ini
                    </h5>
                    <small class=" " style="color:#50200C">{{ now()->format('l, F j, Y') }}</small>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Refresh" onclick="location.reload()">
                        <i class="fas fa-sync-alt" style="color:#50200C"></i>
                    </button>
                </div>
            </div>

            <div class="card-body p-0" style="color:#50200C">
                <div class="table-responsive">
                    <table class="table table-lh mb-0">
                        <thead>
                            <tr>
                                <th>Tamu</th>
                                <th>Kamar</th>
                                <th>Check-in/Out</th>
                                <th>Sarapan</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $transaction->customer->user->getAvatar() }}"
                                                 class="rounded-circle me-3" width="40" height="40" alt="">

                                            <div>
                                                <div class="fw-medium">
                                                    <a href="{{ route('customer.show', ['customer' => $transaction->customer->id]) }}"
                                                       class=" " style="color:#50200C">
                                                        {{ $transaction->customer->name }}
                                                    </a>
                                                </div>
                                                <div class="small" style="color:#50200C">ID: {{ $transaction->customer->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-medium">
                                            <a href="{{ route('room.show', ['room' => $transaction->room->id]) }}"
                                               class=" " style="color:#50200C">
                                                Room {{ $transaction->room->number }}
                                            </a>
                                        </div>
                                        <div class="small" style="color:#50200C">{{ $transaction->room->type->name ?? 'Standard' }}</div>
                                    </td>

                                    <td class="small" style="color:#50200C">
                                        <div><strong>In:</strong> {{ Helper::dateFormat($transaction->check_in) }}</div>
                                        <div><strong>Out:</strong> {{ Helper::dateFormat($transaction->check_out) }}</div>
                                    </td>

                                    <td>
                                        @if($transaction->breakfast == 'Yes')
                                            <span class="badge badge-lh" style="background-color: #A8D5BA; color: #50200C;">Ya</span>
                                        @else
                                            <span class="badge badge-lh" style="background-color: #F2C2B8; color: #50200C;">Tidak</span>
                                        @endif
                                    </td>

                                    <td class="fw-medium" style="color:#50200C">
                                        {{ Helper::convertToRupiah($transaction->total_price ?? $transaction->getTotalPrice()) }}
                                    </td>

                                    <td>
                                       @if($transaction->status == 'Check In')
                                        {{-- Status Check In (Hijau) --}}
                                        <span class="badge badge-lh" style="background-color: #8FB8E1; color: #50200C;">
                                            <i class="fas fa-check-circle me-1"></i>Check In
                                        </span>
                                    @elseif($transaction->status == 'Reservation')
                                        {{-- Status Reservasi (Kuning) --}}
                                        <span class="badge badge-lh" style="background-color: #FAE8A4; color: #50200C;">
                                            <i class="fas fa-clock me-1"></i>Belum Check In
                                        </span>
                                    @else
                                        {{-- Status Lainnya --}}
                                        <span class="badge bg-secondary badge-lh">{{ $transaction->status }}</span>
                                    @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5" style="color:#50200C">
                                        <div class=" ">
                                            <i class="fas fa-bed mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p class="mb-0">Tidak ada tamu yang check-in hari ini</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- <div class="card-footer text-center">
                <a href="{{ route('transaction.index') }}" class="text-decoration-none fw-bold small">
                    Lihat Semua Reservasi <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div> -->
        </div>
    </div>


    <!-- ===================== TAMU BULANAN (KANAN) ===================== -->
    <div class="col-lg-4 mb-4">
        <div class="card card-lh h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold" style="color:#50200C">
                    <i class="fas fa-chart-line me-2"></i>
                    Tamu Bulanan
                </h5>
                <small class=" " style="color:#50200C">
                    Tamu Bulanan untuk {{ Helper::thisMonth() }}/{{ Helper::thisYear() }}
                </small>
            </div>

            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="text-center" style="color:#50200C">
                            <div class="h2 mb-0">{{ count($transactions) }}</div>
                            <small class=" ">Jumlah Tamu Bulan Ini</small>
                        </div>
                    </div>
                </div>

                <div class="position-relative mb-4">
                    <canvas
                        this-year="{{ Helper::thisYear() }}"
                        this-month="{{ Helper::thisMonth() }}"
                        id="visitors-chart"
                        height="200"
                        class="chartjs-render-monitor">
                    </canvas>
                </div>

                <div class="d-flex justify-content-between text-center">
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-center mb-1" style="color:#50200C">
                            <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #50200C;""></div>
                            <small class=" ">Bulan Ini</small>
                        </div>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex align-items-center justify-content-center mb-1" style="color:#50200C">
                            <div class="rounded me-2" style="width: 12px; height: 12px; background-color: #C49A6C;"></div>
                            <small class="text-muted">Bulan Lalu</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card card-lh">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold" style="color:#50200C">
                            <i class="fas fa-bolt me-2"></i>
                            Akses Cepat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3" style="color:#50200C">
                                <a href="{{ route('transaction.reservation.createIdentity') }}"
                                   class="btn btn-lh w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                   style="min-height: 80px; background-color: #A8D5BA">
                                    <i class="fas fa-plus-circle mb-2" style="font-size: 1.5rem; color:#50200C"></i>
                                    <span style="color:#50200C">Tambah Reservasi Kamar</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3" style="color:#50200C">
                                <a href="{{ route('customer.index') }}"
                                   class="btn btn-lh w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                   style="min-height: 80px; background-color: #8FB8E1">
                                    <i class="fas fa-users mb-2" style="font-size: 1.5rem; color:#50200C"></i>
                                    <span style="color:#50200C">Kelola Pelanggan</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3" style="color:#50200C">
                                <a href="{{ route('room.index') }}"
                                   class="btn btn-lh w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                   style="min-height: 80px; background-color: #C49A6C">
                                    <i class="fas fa-bed mb-2" style="font-size: 1.5rem; color:#50200C"></i>
                                    <span style="color:#50200C">Manajemen Kamar</span>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3" style="color:#50200C">
                                <a href="{{ route('laporan.kamar.index') }}"
                                   class="btn btn-lh w-100 h-100 d-flex flex-column align-items-center justify-content-center"
                                   style="min-height: 80px; background-color: #F2C2B8">
                                    <i class="fas fa-file-invoice mb-2" style="font-size: 1.5rem; color:#50200C"></i>
                                    <span style="color:#50200C">Laporan Kamar Hotel</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @section('footer')
    <script src="{{ asset('style/js/chart.min.js') }}"></script>
    <script src="{{ asset('style/js/guestsChart.js') }}"></script>
    <script>
        function reloadJs(src) {
            src = $('script[src$="' + src + '"]').attr("src");
            $('script[src$="' + src + '"]').remove();
            $('<script/>').attr('src', src).appendTo('head');
        }

        Echo.channel('dashboard')
            .listen('.dashboard.event', (e) => {
                $("#dashboard").hide()
                $("#dashboard").load(window.location.href + " #dashboard");
                $("#dashboard").show(150)
                reloadJs('style/js/guestsChart.js');
                toastr.warning(e.message, "Hello, {{ auth()->user()->name }}");
            })
    </script>
    <!-- askjfnsa -->
@endsection --}}
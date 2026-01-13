@extends('template.master')
@section('title', 'Room Detail')

@section('content')
<div class="container mt-4">
    
    {{-- BARIS UTAMA (ROW) --}}
    <div class="row">

        {{-- ================================================= --}}
        {{-- 1. KOLOM KIRI (3): Info Tamu --}}
        {{-- ================================================= --}}
        <div class="col-md-3 mb-3">
            @if (!empty($customer))
                <div class="card shadow-sm h-100">
                    {{-- Avatar Logic --}}
                    @php
                        if ($customer->user) {
                            $avatar = $customer->user->getAvatar();
                            $email  = $customer->user->email;
                        } else {
                            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=50200C&color=fff';
                            $email  = '-';
                        }
                    @endphp

                    <img class="myImages card-img-top" src="{{ $avatar }}"
                        style="object-fit: cover; height:250px; border-top-right-radius: 0.5rem; border-top-left-radius: 0.5rem;">
                    
                    <div class="card-body">
                        <div class="card-text">
                            <div class="row">
                                <div class="col-lg-12" style="color: #50200C;">
                                    <h5 class="mt-0 fw-bold">{{ $customer->name }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless small bg-soft-cream">
                                            <tr>
                                                <td width="15%"><i class="fas fa-envelope" style="color: #50200C;"></i></td>
                                                <td>{{ $email }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-user-md" style="color: #50200C;"></i></td>
                                                <td>{{ $customer->job ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-map-marker-alt" style="color: #50200C;"></i></td>
                                                <td>{{ $customer->address ?? '-' }}</td>
                                            </tr>
                                            @php
                                                $age = $customer->birthdate ? \Carbon\Carbon::parse($customer->birthdate)->age . ' Tahun' : '-';
                                            @endphp
                                            <tr>
                                                <td><i class="fas fa-birthday-cake" style="color: #50200C;"></i></td>
                                                <td>{{ $age }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Jika Kosong --}}
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center py-5 d-flex flex-column justify-content-center" style="color: #50200C;">
                        <i class="fas fa-user-slash fa-3x mb-3"></i>
                        <h4>Kamar Kosong</h4>
                        <p class="text-muted">Tidak ada tamu yang sedang menginap.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ================================================= --}}
        {{-- 2. KOLOM TENGAH (5): Detail Kamar --}}
        {{-- ================================================= --}}
        <div class="col-md-5 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #F7F3E4; color: #50200C;">
                    <div>
                        <h3 class="mb-0">Kamar {{ $room->number }}</h3>
                        <small>{{ $room->name }}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-search-custom" data-bs-toggle="modal" data-bs-target="#imageModal"
                            style="color: #50200C; border-color: #50200C !important;">
                        <i class="fas fa-camera me-1"></i> Ganti Gambar
                    </button>
                </div>
                
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Status --}}
                        <div class="col-12" style="color: #50200C;">
                            @php
                                $status = $room->dynamic_status; 
                                $badgeClass = match($status) {
                                    'Available' => 'bg-success',
                                    'Occupied' => 'bg-danger',
                                    'Cleaning' => 'bg-warning text-dark',
                                    'Reserved' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <div class="p-2 border rounded d-flex justify-content-between align-items-center">
                                <strong>Status Saat Ini:</strong>
                                <span class="badge {{ $badgeClass }} fs-6"
                                      style="background-color: #FAE8A4 !important; color: #50200C !important;">{{ $status }}</span>
                            </div>
                        </div>

                        {{-- Info Grid --}}
                        <div class="col-6" style="color: #50200C;">
                            <small class="d-block">Tipe Kamar</small>
                            <h6 class="fw-bold">{{ $room->type->name }}</h6>
                        </div>
                        <div class="col-6" style="color: #50200C;">
                            <small class="d-block">Harga per Malam</small>
                            <h6 class="fw-bold">Rp {{ number_format($room->price, 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-6" style="color: #50200C;">
                            <small class="d-block">Kapasitas</small>
                            <h6 class="fw-bold">{{ $room->capacity }} Orang</h6>
                        </div>
                        <div class="col-6" style="color: #50200C;">
                            <small class="d-block">Luas Area</small>
                            <h6 class="fw-bold">{{ $room->area_sqm ?? '-' }} m²</h6>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Fasilitas --}}
                        <div class="col-12" style="color: #50200C;">
                            <strong class="d-block mb-1"><i class="fas fa-tv me-1"></i> Fasilitas Kamar</strong>
                            <p class="small mb-0">
                                {{ $room->room_facilities ?? 'Tidak ada data fasilitas.' }}
                            </p>
                        </div>
                        <div class="col-12" style="color: #50200C;">
                            <strong class="d-block mb-1"><i class="fas fa-bath me-1"></i> Fasilitas Kamar Mandi</strong>
                            <p class="small mb-0">
                                {{ $room->bathroom_facilities ?? 'Tidak ada data fasilitas.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- 3. KOLOM KANAN (4): Gambar Kamar --}}
        {{-- ================================================= --}}
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="color: #50200C;">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title">Gambar Utama</h5>
                </div>
                <div class="card-body">
                    <div class="position-relative rounded overflow-hidden shadow-sm">
                        <img src="{{ $room->getImage() }}" 
                             alt="Room Image" 
                             class="w-100" 
                             style="height: 300px; object-fit: cover;"
                             onerror="this.src='{{ asset('img/default/default-room.png') }}'">
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- END ROW UTAMA --}}

    {{-- TOMBOL KEMBALI DI BAWAH --}}
    <div class="row mt-3 pb-4">
        <div class="col-12">
            <a href="{{ route('room.index') }}" class="btn shadow-sm px-4 text-white" style="background-color: #50200C;">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

{{-- MODAL GANTI GAMBAR (STYLE TETAP) --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="color: #50200C;">
                <h5 class="modal-title">Update Gambar Kamar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('room.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Hidden Inputs --}}
                    <input type="hidden" name="type_id" value="{{ $room->type_id }}">
                    <input type="hidden" name="number" value="{{ $room->number }}">
                    <input type="hidden" name="name" value="{{ $room->name }}">
                    <input type="hidden" name="capacity" value="{{ $room->capacity }}">
                    <input type="hidden" name="price" value="{{ $room->price }}">
                    
                    <div class="mb-3">
                        <label for="image" class="form-label" style="color: #50200C;">Pilih Gambar Baru</label>
                        <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
                        <small style="color: #50200C;">Format: JPG, PNG. Maks: 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Gambar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer')
    @if(session('success'))
        <script>
            if(typeof toastr !== 'undefined'){
                toastr.success("{{ session('success') }}", "Berhasil");
            }
        </script>
    @endif
@endsection
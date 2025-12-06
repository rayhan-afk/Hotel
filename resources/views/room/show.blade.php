@extends('template.master')
@section('title', 'Room Detail')
@section('content')
    <div class="row">
        {{-- KOLOM KIRI: Info Tamu (Jika Sedang Terisi) --}}
        <div class="col-md-3">
            @if (!empty($customer))
                <div class="card shadow-sm justify-content-start" style="min-height:350px;">
                    {{-- Mengambil avatar user dari relasi customer -> user --}}
                    <img class="myImages" src="{{ $customer->user->getAvatar() }}"
                        style="object-fit: cover; height:250px; border-top-right-radius: 0.5rem; border-top-left-radius: 0.5rem;">
                    <div class="card-body">
                        <div class="card-text">
                            <div class="row">
                                <div class="col-lg-12">
                                    <h5 class="mt-0">{{ $customer->name }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="10%"><i class="fas fa-envelope text-secondary"></i></td>
                                                <td>{{ $customer->user->email }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-user-md text-secondary"></i></td>
                                                <td>{{ $customer->job }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-map-marker-alt text-secondary"></i></td>
                                                <td>{{ $customer->address }}</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-birthday-cake text-secondary"></i></td>
                                                <td>{{ $customer->birthdate }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                        <h4>Kamar Kosong</h4>
                        <p class="text-muted">Tidak ada tamu yang sedang menginap.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- KOLOM TENGAH: Detail Kamar (DATA BARU) --}}
        <div class="col-md-5 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        {{-- Tampilkan Nomor dan Nama Kamar --}}
                        <h3 class="mb-0">Kamar {{ $room->number }}</h3>
                        <small class="text-muted">{{ $room->name }}</small>
                    </div>
                    {{-- Tombol Edit Cepat --}}
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#imageModal">
                        <i class="fas fa-camera me-1"></i> Ganti Gambar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Status Dinamis --}}
                        <div class="col-12">
                            @php
                                $status = $room->dynamic_status; // Menggunakan Accessor di Model Room
                                $badgeClass = match($status) {
                                    'Available' => 'bg-success',
                                    'Occupied' => 'bg-danger',
                                    'Cleaning' => 'bg-warning text-dark',
                                    'Reserved' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                <strong>Status Saat Ini:</strong>
                                <span class="badge {{ $badgeClass }} fs-6">{{ $status }}</span>
                            </div>
                        </div>

                        {{-- Info Dasar --}}
                        <div class="col-6">
                            <small class="text-muted d-block">Tipe Kamar</small>
                            <h6>{{ $room->type->name }}</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Harga per Malam</small>
                            <h6 class="text-primary">Rp {{ number_format($room->price, 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Kapasitas</small>
                            <h6>{{ $room->capacity }} Orang</h6>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Luas Area</small>
                            <h6>{{ $room->area_sqm ?? '-' }} m²</h6>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        {{-- Fasilitas Kamar --}}
                        <div class="col-12">
                            <strong class="d-block mb-1"><i class="fas fa-tv me-1 text-secondary"></i> Fasilitas Kamar</strong>
                            <p class="text-muted small mb-0">
                                {{ $room->room_facilities ?? 'Tidak ada data fasilitas.' }}
                            </p>
                        </div>

                        {{-- Fasilitas Kamar Mandi --}}
                        <div class="col-12">
                            <strong class="d-block mb-1"><i class="fas fa-bath me-1 text-secondary"></i> Fasilitas Kamar Mandi</strong>
                            <p class="text-muted small mb-0">
                                {{ $room->bathroom_facilities ?? 'Tidak ada data fasilitas.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Gambar Kamar (UPDATED: Main Image) --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title">Gambar Utama</h5>
                </div>
                <div class="card-body">
                    {{-- Menampilkan Gambar Utama dari path database --}}
                    <div class="position-relative rounded overflow-hidden shadow-sm">
                        @if($room->main_image_path)
                            <img src="{{ asset($room->main_image_path) }}" alt="Room Image" class="w-100" 
                                style="height: 300px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex justify-content-center align-items-center" style="height: 300px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p>Tidak ada gambar</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Gambar (Updated Logic) -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Gambar Kamar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- Form mengarah ke route update, karena kita mengupdate kolom main_image_path di tabel rooms --}}
                <form action="{{ route('room.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        {{-- Trik: Kita kirim input hidden untuk field mandatory lain agar validasi tidak gagal --}}
                        <input type="hidden" name="type_id" value="{{ $room->type_id }}">
                        <input type="hidden" name="number" value="{{ $room->number }}">
                        <input type="hidden" name="name" value="{{ $room->name }}">
                        <input type="hidden" name="capacity" value="{{ $room->capacity }}">
                        <input type="hidden" name="price" value="{{ $room->price }}">
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Pilih Gambar Baru</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
                            <small class="text-muted">Format: JPG, PNG. Maks: 2MB.</small>
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
            toastr.success("{{ session('success') }}", "Berhasil");
        </script>
    @endif
@endsection
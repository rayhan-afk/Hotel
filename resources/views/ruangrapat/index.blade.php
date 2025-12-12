@extends('template.master')
@section('title', 'Ruang Rapat Management')

@section('content')
<div class="container-fluid">

    <div class="row mt-2 mb-2">
        <div class="col-lg-6 mb-2">
        </div>
        <div class="col-lg-6 mb-2">
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2"> 
                {{-- Tombol Tambah Paket --}}
                <a href="{{ route('ruangrapat.create') }}" id="add-button" class="add-room-btn">
                    <i class="fas fa-plus"></i>
                    Tambah Paket Ruang Rapat
                </a>
                
                {{-- Tombol Buat Reservasi --}}
                <a href="{{ route('rapat.reservation.showStep1') }}" class="btn btn-hotel-primary add-room-btn" style="height: auto; line-height: 1.5;"> 
                    <i class="fas fa-calendar-plus me-1"></i>
                    Buat Reservasi Ruang Rapat
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        
        {{-- ======================================================= --}}
        {{-- JADWAL RESERVASI (Akan Datang) --}}
        {{-- ======================================================= --}}
        <div class="col-lg-6">
            <div class="row my-2 mt-4 ms-1">
                <div class="col-lg-12">
                    <h5 style="color: #50200C;"><i class="fas fa-calendar-check me-2"></i>Jadwal Reservasi</h5>
                </div>
            </div>
            <div class="card p-0">
                <div class="card-body">
                    <div class="table-responsive" style="max-width: calc(100vw - 50px)">
                        <table class="table table-sm table-hover">
                            <thead style="background-color: #F7F3E4;">
                                <tr>
                                    <th>Instansi/Perusahaan</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Paket</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="reservasi-jadwal-body">
                                @forelse ($rapatTransactionsJadwal as $transaction)
                                    @php
                                        $fullStartTime = $transaction->tanggal_pemakaian . ' ' . $transaction->waktu_mulai;
                                    @endphp
                                    <tr data-start-time-str="{{ $fullStartTime }}" class="warna-coklat">
                                        <td>{{ $transaction->rapatCustomer->instansi ?? '-' }}</td>
                                        <td>{{ Helper::dateFormat($transaction->tanggal_pemakaian) }}</td>
                                        <td>{{ $transaction->waktu_mulai }} - {{ $transaction->waktu_selesai }}</td>
                                        <td>{{ $transaction->ruangRapatPaket->name }}</td>
                                        <td>
                                            <span class="badge {{ $transaction->status_pembayaran == 'Paid' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $transaction->status_pembayaran == 'Paid' ? 'Lunas' : $transaction->status_pembayaran }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- TOMBOL CANCEL (TEKS) --}}
                                            {{-- Class 'px-3' ditambahkan agar tombol agak lebar --}}
                                            <button type="button" class="btn btn-danger btn-sm rounded shadow-sm border m-0 delete-btn px-3"
                                                data-id="{{ $transaction->id }}" 
                                                data-name="{{ $transaction->rapatCustomer->nama ?? 'Reservasi' }}"
                                                data-route="{{ route('rapat.transaction.destroy', $transaction->id) }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Batalkan & Hapus Data">
                                                Cancel
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center" style="color:#50200C;"> Tidak ada jadwal reservasi.
                                        </td>
                                    </tr>
                                @endforelse 
                            </tbody>
                        </table>
                        {{ $rapatTransactionsJadwal->appends([
                            'berlangsung_page' => $rapatTransactionsBerlangsung->currentPage(),
                            'expired_page' => $rapatTransactionsExpired->currentPage(), 
                            'search' => request('search')
                        ])->links('template.paginationlinks') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- RESERVASI BERLANGSUNG (Sedang Berjalan) --}}
        {{-- ======================================================= --}}
        <div class="col-lg-6">
            <div class="row my-2 mt-4 ms-1">
                <div class="col-lg-12">
                    <h5>
                        <i class="fas fa-dot-circle me-2" style="color: #8B4513;"></i>
                        <span style="color:#50200C;">Reservasi Berlangsung</span>
                    </h5>
                </div>
            </div>
            <div class="card p-0 border-danger"> 
                <div class="card-body">
                    <div class="table-responsive" style="max-width: calc(100vw - 50px)">
                        <table class="table table-sm table-hover">
                            <thead class="bg-danger text-white">
                                <tr>
                                    <th>Instansi/Perusahaan</th>
                                    <th>Waktu Selesai</th>
                                    <th>Paket</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="reservasi-berlangsung-body">
                                @forelse ($rapatTransactionsBerlangsung as $transaction)
                                @php
                                    $fullEndTime = $transaction->tanggal_pemakaian . ' ' . $transaction->waktu_selesai;
                                @endphp
                                <tr data-end-time-str="{{ $fullEndTime }}" class="warna-coklat">
                                    <td>{{ $transaction->rapatCustomer->instansi ?? '-' }}</td>
                                    <td>{{ $transaction->waktu_selesai }}</td>
                                    <td>{{ $transaction->ruangRapatPaket->name }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->status_pembayaran == 'Paid' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $transaction->status_pembayaran == 'Paid' ? 'Lunas' : $transaction->status_pembayaran }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center" style="color:#50200C;"> Tidak ada reservasi berlangsung.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $rapatTransactionsBerlangsung->appends([
                            'jadwal_page' => $rapatTransactionsJadwal->currentPage(),
                            'expired_page' => $rapatTransactionsExpired->currentPage(), 
                            'search' => request('search')
                        ])->links('template.paginationlinks') }}
                    </div>
                </div>
            </div>
        </div>
    </div> 

    <hr class="my-2"> 

    {{-- ======================================================= --}}
    {{-- MANAJEMEN PAKET RUANG RAPAT --}}
    {{-- ======================================================= --}}
    <div class="row">
        <div class="col-12">
    <div class="professional-table-container">
        <div class="table-header">
            <h4><i class="fas fa-handshake me-2"></i>Manajemen Paket Ruang Rapat</h4>
            <p>Kelola daftar paket ruang rapat yang tersedia di hotel</p>
        </div>
        <div class="table-responsive">
            <table id="ruangrapat-table" class="professional-table table" style="width: 100%;">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Paket</th>
                        <th scope="col">Isi Paket</th>
                        <th scope="col">Fasilitas</th>
                        <th scope="col">Harga</th>
                        <th scope="col"><i class="fas fa-cog me-1"></i>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- DataTables akan memuat data di sini --}}
                </tbody>
            </table>
        </div>
        <div class="table-footer">
        </div>
    </div>
</div>
@endsection

{{-- Modal Konfirmasi Hapus --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus reservasi **<span id="deleteItemName"></span>** ini secara permanen?
                <br>
                <small class="text-danger">Aksi ini tidak dapat dibatalkan.</small>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('footer')
<script>
    // Inisialisasi DataTable untuk Manajemen Paket Ruang Rapat
    $(document).ready(function() {
        $('#ruangrapat-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('ruangrapat.index') }}", // Sesuaikan dengan route data
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'isi_paket', name: 'isi_paket' },
                { data: 'fasilitas', name: 'fasilitas' },
                { 
                    data: 'harga', 
                    name: 'harga', 
                    render: function(data, type, row) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(data);
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
            }
        });
        
        // --- Modal Delete Ruang Rapat & Paket (Tabel Bawah) ---
        $(document).on('click', '.delete-btn-paket', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var route = $(this).data('route');
            
            $('#deleteItemName').text(name);
            $('#deleteForm').attr('action', route);
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
        
        // --- Modal Delete Reservasi (Tabel Atas) ---
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var route = $(this).data('route');
            
            $('#deleteItemName').text("Reservasi milik " + name);
            $('#deleteForm').attr('action', route);
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });

    // Pengecekan status waktu otomatis (Jadwal -> Berlangsung -> Expired)
    function checkReservationStatusChanges() {
        const now = new Date();
        let shouldReload = false;

        const checkTimeAndHide = (bodyId, timeAttr) => {
            const tableBody = document.getElementById(bodyId);
            if (!tableBody) return;

            tableBody.querySelectorAll(`tr[${timeAttr}-str]`).forEach(row => {
                // HANYA proses baris yang saat ini terlihat
                if (row.style.display !== 'none') {
                    const timeStr = row.getAttribute(`${timeAttr}-str`);
                    // Ganti spasi dengan T untuk parsing ISO 8601 yang andal
                    const dateTime = new Date(timeStr.replace(' ', 'T'));
                    
                    if (now >= dateTime) {
                        row.style.display = 'none'; // Sembunyikan baris
                        shouldReload = true; // Tandai untuk reload
                    }
                }
            });
        };

        // --- 1. Cek Tabel JADWAL RESERVASI (Pindah ke Berlangsung) ---
        checkTimeAndHide('reservasi-jadwal-body', 'data-start-time');

        // --- 2. Cek Tabel RESERVASI BERLANGSUNG (Pindah ke Selesai) ---
        checkTimeAndHide('reservasi-berlangsung-body', 'data-end-time');

        // --- 3. Trigger Reload jika ada Perubahan (Item baru saja disembunyikan) ---
        if (shouldReload) {
            setTimeout(() => {
                window.location.reload();
            }, 500); 
        }
    }

    setInterval(checkReservationStatusChanges, 1000);
</script>
@endsection
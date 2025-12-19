@extends('template.master')
@section('title', 'Konfirmasi Reservasi')
@section('head')
    <link rel="stylesheet" href="{{ asset('style/css/progress-indication.css') }}">
    <style>
        .invoice-card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        .invoice-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .invoice-body {
            padding: 30px;
        }
        .table-invoice th {
            font-weight: 600;
            color: #555;
        }
        .table-invoice td {
            vertical-align: middle;
        }
        .total-row {
            background-color: #eef2f7;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .breakfast-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .btn-download-invoice {
            background: linear-gradient(135deg, #A8C9E8 0%, #8FB8E1 100%) !important;
            border: 1px solid #8FB8E1 !important;
            border-radius: 0.75rem !important;
            color: #50200C !important;
            padding: 0.75rem 1.2rem !important;
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            line-height: 1.5rem !important;
            box-sizing: border-box !important;
            cursor: pointer !important;
            text-align: center !important;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(143, 184, 225, 0.3) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .btn-download-invoice:hover {
            background: linear-gradient(135deg, #8FB8E1 0%, #7DA3CC 100%) !important;
            border-color: #7DA3CC !important;
            box-shadow: 0 8px 20px rgba(143, 184, 225, 0.45) !important;
            transform: translateY(-2px) !important;
            color: #50200C !important;
        }
        
        .bg-custom-list {
            background-color: #fff; 
        }
    </style>
@endsection
@section('content')
    @include('transaction.reservation.progressbar')
    
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            {{-- Kolom Kiri: Invoice Tagihan --}}
            <div class="col-lg-8">
                <div class="card invoice-card mb-4">
                    <div class="invoice-header d-flex justify-content-between align-items-center" style="background-color: #F7F3E4">
                        <h4 class="mb-0 fw-bold" style="color:#50200C"><i class="fas fa-file-invoice me-2"></i>Rincian Tagihan</h4>
                    </div>
                    <div class="invoice-body">
                        
                        {{-- Detail Reservasi --}}
                        <div class="row mb-4">
                            <div class="col-md-6" style="color:#50200C">
                                <p class="mb-1 small text-uppercase fw-bold">Info Kamar</p>
                                <h5 class="fw-bold">{{ $room->number }} - {{ $room->type->name }}</h5>
                                <p class="mb-0"><i class="fas fa-user me-1"></i> {{ $room->capacity }} Orang</p>
                                
                                {{-- [BARU] Tampilkan Grup Customer agar admin tau kenapa harganya sekian --}}
                                <div class="mt-2">
                                    <span class="badge bg-secondary">Rate: {{ $customer->customer_group ?? 'General' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end" style="color:#50200C">
                                <p class="mb-1 small text-uppercase fw-bold">Tanggal Menginap</p>
                                <p class="mb-0 fw-bold">{{ Helper::dateFormat($stayFrom) }} — {{ Helper::dateFormat($stayUntil) }}</p>
                                <p class="mb-0">{{ $dayDifference }} Malam</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <form method="POST" 
                            id="reservation-form" 
                            {{-- [PENTING] Data harga ini tidak lagi dipakai JS hitung manual, tapi JS akan pakai variabel Blade di bawah --}}
                            action="{{ route('transaction.reservation.payDownPayment', ['customer' => $customer->id, 'room' => $room->id]) }}">
                            @csrf
                            
                            <input type="hidden" name="check_in" value="{{ $stayFrom }}">
                            <input type="hidden" name="check_out" value="{{ $stayUntil }}">
                            {{-- Value default adalah DownPayment yang sudah dihitung di controller (Kamar + Pajak) --}}
                            <input type="hidden" name="total_price" id="input_total_price" value="{{ $downPayment }}"> 

                            {{-- Tabel Rincian Biaya --}}
                            <div class="table-responsive">
                                <table class="table table-invoice" style="color:#50200C">
                                    <thead>
                                        <tr>
                                            <th>Deskripsi Item</th>
                                            <th class="text-end">Info Harga</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Item 1: Sewa Kamar --}}
                                        <tr>
                                            <td>
                                                <span class="fw-bold" style="color:#50200C">Sewa Kamar</span>
                                                <div class="small" style="color:#50200C">
                                                    {{ $customer->customer_group ?? 'General' }} Rate
                                                </div>
                                            </td>
                                            <td class="text-end" style="color:#50200C">
                                                {{-- Karena harga per malam bisa beda (Weekday/Weekend), kita tulis dinamis --}}
                                                <small class="text-muted"><i>Sesuai Tanggal</i></small>
                                            </td>
                                            <td class="text-center" style="color:#50200C">{{ $dayDifference }} Malam</td>
                                            <td class="text-end fw-bold" style="color:#50200C">
                                                {{-- [PENTING] Gunakan variabel $roomPriceTotal dari Controller --}}
                                                {{ Helper::convertToRupiah($roomPriceTotal) }}
                                            </td>
                                        </tr>

                                        {{-- Item 2: Sarapan (Dinamis) --}}
                                        <tr id="row_breakfast" style="display: none;">
                                            <td>
                                                <span class="fw-bold" style="color:#50200C">Paket Sarapan</span>
                                                <div class="small" style="color:#50200C">
                                                    <span class="breakfast-badge" style="color:#50200C"><i class="fas fa-utensils me-1"></i> Max 2 Orang</span>
                                                </div>
                                            </td>
                                            <td class="text-end" style="color:#50200C">Rp 100.000</td>
                                            <td class="text-center" style="color:#50200C">{{ $dayDifference }} Malam</td>
                                            <td class="text-end fw-bold" style="color:#50200C" id="display_breakfast_total">Rp 0</td>
                                        </tr>

                                        {{-- Item 3: Pajak PB1 --}}
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold" style="color:#50200C">
                                                Pajak PB1 (10%)
                                            </td>
                                            <td class="text-end fw-bold" style="color:#50200C" id="display_tax">
                                                {{-- Menampilkan pajak awal (dari Controller) --}}
                                                {{ Helper::convertToRupiah($minimumTax) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        {{-- Opsi Tambahan --}}
                                        <tr>
                                            <td colspan="4" class="bg-light p-3">
                                                <div class="d-flex align-items-center justify-content-end" style="color:#50200C">
                                                    <label for="breakfast_select" class="fw-bold me-3 mb-0">
                                                        <i class="fas fa-coffee me-1"></i> Tambah Sarapan?
                                                    </label>
                                                    <select class="form-select w-auto border-primary" style="color:#50200C" id="breakfast_select" name="breakfast">
                                                        <option value="No" selected>Tidak</option>
                                                        <option value="Yes">Ya, Tambahkan (+Rp 100.000/malam)</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        {{-- Total Bayar (Lunas) --}}
                                        <tr class="total-row total-text">
                                            <td colspan="3" class="text-end">Total yang Harus Dibayar</td>
                                            <td class="text-end" id="display_total_price">
                                                {{-- Menampilkan Total Awal (Kamar + Pajak) --}}
                                                {{ Helper::convertToRupiah($downPayment) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>

                        {{-- Tombol Aksi --}}
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('transaction.reservation.chooseRoom', ['customer' => $customer->id]) }}?check_in={{$stayFrom}}&check_out={{$stayUntil}}&count_person={{$countPerson}}" 
                               class="btn btn-modal-close px-4 py-2" id="btn-modal-close">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>

                            <div class="d-flex gap-2">
                                <a href="{{ route('transaction.reservation.previewInvoice', ['customer' => $customer->id, 'room' => $room->id, 'from' => $stayFrom, 'to' => $stayUntil]) }}?breakfast=No" 
                                target="_blank" 
                                class="btn-download-invoice me-2" 
                                id="btn-download-invoice">
                                    <i class="fas fa-file-download me-2"></i>Unduh Kwitansi
                                </a>

                                <button type="submit" form="reservation-form" class="btn btn-modal-save px-3 py-2" id="btn-modal-save">
                                    Konfirmasi & Bayar Lunas <i class="fas fa-money-bill-wave ms-2"></i>
                                </button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Data Pelanggan --}}
            <div class="col-lg-4">
                <div class="card invoice-card border-0 sticky-top" style="top: 20px; z-index: 1; background-color: #F7F3E4">
                    <div class="card-header text-center py-4 border-0">
                        {{-- Handle jika getAvatar tidak ada, fallback --}}
                        @php
                            $avatar = method_exists($customer->user, 'getAvatar') ? $customer->user->getAvatar() : asset('img/default/default-user.jpg');
                        @endphp
                        <img src="{{ $avatar }}"
                            class="rounded-circle shadow border p-1" 
                            style="width: 100px; height: 100px; object-fit: cover;">
                        <h5 class="mt-3 mb-0 fw-bold" style="color:#50200C">{{ $customer->name }}</h5>
                        <p class="badge small mb-0" style="background-color: #C49A6C">{{ $customer->job }}</p>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush bg-custom-list">
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between" style="color:#50200C">
                                <span class=" "><i class="fas fa-users me-2"></i> Grup Tamu</span>
                                <span class="fw-bold text-primary">{{ $customer->customer_group ?? 'General' }}</span>
                            </li>
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between" style="color:#50200C">
                                <span class=" "><i class="fas fa-venus-mars me-2"></i> Jenis Kelamin</span>
                                <span class="fw-medium">
                                    <i class="fas {{ $customer->gender == 'Male' ? 'fa-male' : 'fa-female' }}"
                                    style="color: {{ $customer->gender == 'Male' ? '#A8D5BA' : '#F2C2B8' }};"></i>
                                    {{ $customer->gender == 'Male' ? 'Laki-laki' : 'Perempuan' }}
                                </span>
                            </li>
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between" style="color:#50200C">
                                <span class=" "><i class="fas fa-birthday-cake me-2"></i> Lahir</span>
                                <span class="fw-medium">{{ $customer->birthdate }}</span>
                            </li>
                            <li class="list-group-item px-4 py-3" style="color:#50200C">
                                <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Alamat</div>
                                <p class="mb-0 fw-medium small">{{ $customer->address }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
<script>
    // [PENTING] KITA GUNAKAN HARGA TOTAL DARI CONTROLLER (SULTAN MODE)
    // Bukan lagi room->price * days, karena harga per hari bisa beda.
    // Variabel ini sudah berisi Total Harga Kamar (Weekday + Weekend + Diskon Grup)
    const baseRoomTotal = {{ $roomPriceTotal }}; 
    
    const dayCount = {{ $dayDifference }};
    const breakfastPricePerDay = 100000; 

    // Elemen DOM
    const breakfastSelect = document.getElementById('breakfast_select');
    const rowBreakfast = document.getElementById('row_breakfast');
    const displayBreakfastTotal = document.getElementById('display_breakfast_total');
    const displayTax = document.getElementById('display_tax');
    const displayTotalPrice = document.getElementById('display_total_price');
    const inputTotalPrice = document.getElementById('input_total_price');
    const btnDownloadInvoice = document.getElementById('btn-download-invoice');
    
    const baseInvoiceUrl = "{{ route('transaction.reservation.previewInvoice', ['customer' => $customer->id, 'room' => $room->id, 'from' => $stayFrom, 'to' => $stayUntil]) }}";

    // Fungsi Format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR', 
            minimumFractionDigits: 0,
            maximumFractionDigits: 0 
        }).format(number);
    };

    // Event Listener
    breakfastSelect.addEventListener('change', function() {
        // Mulai hitungan dari baseRoomTotal (yang sudah dihitung Controller)
        let currentSubTotal = baseRoomTotal; 
        
        let breakfastParam = 'No';

        if (this.value === 'Yes') {
            const breakfastTotal = breakfastPricePerDay * dayCount;
            currentSubTotal += breakfastTotal; 
            
            rowBreakfast.style.display = 'table-row';
            displayBreakfastTotal.innerText = formatRupiah(breakfastTotal);
            
            breakfastParam = 'Yes';
        } else {
            rowBreakfast.style.display = 'none';
        }

        // --- HITUNG PAJAK & TOTAL ---
        const taxAmount = currentSubTotal * 0.10; 
        const finalTotal = currentSubTotal + taxAmount;

        // Update Tampilan Angka
        displayTax.innerText = formatRupiah(taxAmount);
        displayTotalPrice.innerText = formatRupiah(finalTotal);
        
        // Update Input Hidden
        if(inputTotalPrice) {
            inputTotalPrice.value = finalTotal;
        }

        // Update Link Download
        if (btnDownloadInvoice) {
            btnDownloadInvoice.href = `${baseInvoiceUrl}?breakfast=${breakfastParam}`;
        }
    });
</script>
@endsection
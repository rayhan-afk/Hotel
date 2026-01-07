<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pemesanan Kamar Hotel - {{ $customer->name }} - {{ Helper::dateFormat($date) }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { font-family: sans-serif; background: #fff; }
        .invoice-header { border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; }
        .logo { max-width: 300px; }
        .invoice-title { float: right; color: #555; }
        .table th { background-color: #f8f9fa; }
    </style>
</head>
<body onload="window.print()"> 

    <div class="container mt-5">
        {{-- Header --}}
        <div class="row invoice-header">
            <div class="col-7">
                {{-- Logo Hotel --}}
                <img src="{{ asset('img/logo-anda.png') }}" alt="Logo Hotel" class="logo">
                <br>
                <small>Jl. Sawunggaling No.13, Tamansari, Kec. Bandung Wetan,<br> 
                Kota Bandung, Jawa Barat | Telp. 081917044390</small>
            </div>
            <div class="col-5 text-right">
                <h2 class="invoice-title">GUEST INVOICE</h2>
                <br><br>
                <strong>No: {{ $transaction_code }}</strong><br>
                Tanggal: {{ Helper::dateFormat($date) }}
            </div>
        </div>

        {{-- Info Pelanggan --}}
        <div class="row mb-4">
            <div class="col-6">
                <strong>Nama Tamu:</strong><br>
                <h4>{{ $customer->name }}</h4>
                <p>
                    {{ $customer->address }}<br>
                    No. HP: {{ $customer->phone ?? '-' }}
                </p>
            </div>
            <div class="col-6 text-right">
                <strong>Detail Reservasi:</strong><br>
                Check In: {{ Helper::dateFormat($check_in) }}<br>
                Check Out: {{ Helper::dateFormat($check_out) }}<br>
                Durasi: {{ $days }} Malam
            </div>
        </div>

        {{-- Tabel Item --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                
                {{-- A. SEWA KAMAR (Logic Weekday & Weekend) --}}
                @php
                    $hasBreakdown = isset($weekday_count) && isset($weekend_count) && (($weekday_count + $weekend_count) > 0);
                @endphp

                @if($hasBreakdown)
                    {{-- 1. Baris Weekday --}}
                    @if($weekday_count > 0)
                    <tr>
                        <td>
                            Sewa Kamar Tipe {{ $room->type->name }} (No. {{ $room->number }})
                            <br><small class="text-muted"><i>Rate Weekday</i></small>
                        </td>
                        <td class="text-center">{{ $weekday_count }} Malam</td>
                        <td class="text-right">{{ Helper::convertToRupiah($weekday_price_satuan) }}</td>
                        <td class="text-right">{{ Helper::convertToRupiah($weekday_total) }}</td>
                    </tr>
                    @endif

                    {{-- 2. Baris Weekend --}}
                    @if($weekend_count > 0)
                    <tr>
                        <td>
                            Sewa Kamar Tipe {{ $room->type->name }} (No. {{ $room->number }})
                            <br><small class="text-muted"><i>Rate Weekend</i></small>
                        </td>
                        <td class="text-center">{{ $weekend_count }} Malam</td>
                        <td class="text-right">{{ Helper::convertToRupiah($weekend_price_satuan) }}</td>
                        <td class="text-right">{{ Helper::convertToRupiah($weekend_total) }}</td>
                    </tr>
                    @endif

                @else
                    {{-- FALLBACK --}}
                    <tr>
                        <td>Sewa Kamar Tipe {{ $room->type->name }} (No. {{ $room->number }})</td>
                        <td class="text-center">{{ $days }} Malam</td>
                        <td class="text-right">{{ Helper::convertToRupiah($room->price) }}</td>
                        <td class="text-right">{{ Helper::convertToRupiah($room_price_total) }}</td>
                    </tr>
                @endif


                {{-- B. ITEM LAINNYA --}}
                
                {{-- Sarapan Utama (Masih bagian dari paket kamar) --}}
                @if($breakfast_status == 'Yes')
                <tr>
                    <td>Paket Sarapan (Tamu Utama)</td>
                    <td class="text-center">{{ $days }} Hari</td>
                    <td class="text-right">Rp 100.000</td>
                    <td class="text-right">{{ Helper::convertToRupiah($breakfast_price_total) }}</td>
                </tr>
                @endif

                {{-- [HAPUS] Extra Bed & Extra Breakfast Lama sudah dihapus dari sini --}}

                {{-- === C. BIAYA TAMBAHAN (CHARGES / FO CASHIER) === --}}
                {{-- Extra Bed & Breakfast baru akan muncul otomatis di sini --}}
                @if(isset($transaction) && $transaction->charges->count() > 0)
                    <tr>
                        <td colspan="4" class="font-weight-bold" style="background-color: #f8f9fa; font-size: 0.9em; padding: 5px 10px;">
                            BIAYA TAMBAHAN (Layanan & Services)
                        </td>
                    </tr>
                    @foreach($transaction->charges as $charge)
                    <tr>
                        <td>
                            {{ $charge->item_name }}
                            <br><small class="text-muted"><i>{{ $charge->type }}</i></small>
                        </td>
                        <td class="text-center">{{ $charge->qty }}</td>
                        <td class="text-right">{{ Helper::convertToRupiah($charge->amount) }}</td>
                        <td class="text-right">{{ Helper::convertToRupiah($charge->total) }}</td>
                    </tr>
                    @endforeach
                @endif
                {{-- ======================================================== --}}

            </tbody>
            <tfoot>
                {{-- FOOTER BERSIH: LANGSUNG TOTAL --}}
                <tr class="bg-light">
                    <td colspan="3" class="text-right font-weight-bold" style="font-size: 1.2em;">TOTAL BAYAR</td>
                    <td class="text-right font-weight-bold" style="font-size: 1.2em;">{{ Helper::convertToRupiah($grand_total) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
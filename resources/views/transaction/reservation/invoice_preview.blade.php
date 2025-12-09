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

        .btn-soft-blue {
            background-color: #76A9FA; /* Warna Biru Soft */
            border: none;
            color: white !important; /* Pastikan teks putih */
            transition: all 0.3s ease;
        }

        .btn-soft-blue:hover {
            background-color: #5a8dee; /* Warna sedikit lebih gelap saat di-hover */
            box-shadow: 0 4px 8px rgba(118, 169, 250, 0.3); /* Efek bayangan halus */
            transform: translateY(-1px); /* Efek naik sedikit */
        }
        /* Watermark dihapus */
    </style>
</head>
<body onload="window.print()"> {{-- Otomatis trigger print saat dibuka --}}

    {{-- Watermark dihapus --}}

    <div class="container mt-5">
        {{-- Header --}}
        <div class="row invoice-header">
            <div class="col-7">
                {{-- Logo Hotel Sawunggaling --}}
                <img src="{{ asset('img/logo-anda.png') }}" alt="Logo Hotel" class="logo">
                <br>
                {{-- Alamat Baru --}}
                <small>Jl. Sawunggaling No.13, Tamansari, Kec. Bandung Wetan,<br> 
                Kota Bandung, Jawa Barat | Telp. 081917044390</small>
            </div>
            <div class="col-5 text-right">
                <h2 class="invoice-title">INVOICE RESERVASI</h2>
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
                <p>{{ $customer->address }}<br>
                {{-- Ganti Pekerjaan jadi No HP --}}
                No. HP: {{ $customer->phone ?? '-' }}</p>
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
                {{-- Kamar --}}
                <tr>
                    <td>Sewa Kamar Tipe {{ $room->type->name }} (No. {{ $room->number }})</td>
                    <td class="text-center">{{ $days }} Malam</td>
                    <td class="text-right">{{ Helper::convertToRupiah($room->price) }}</td>
                    <td class="text-right">{{ Helper::convertToRupiah($room_price_total) }}</td>
                </tr>

                {{-- Sarapan (Jika Ada) --}}
                @if($breakfast_status == 'Yes')
                <tr>
                    <td>Paket Sarapan</td>
                    <td class="text-center">{{ $days }} Hari</td>
                    <td class="text-right">Rp 140.000</td>
                    <td class="text-right">{{ Helper::convertToRupiah($breakfast_price_total) }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Subtotal</td>
                    <td class="text-right">{{ Helper::convertToRupiah($room_price_total + $breakfast_price_total) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Pajak PB1 (10%)</td>
                    <td class="text-right">{{ Helper::convertToRupiah($tax) }}</td>
                </tr>
                <tr class="bg-light">
                    <td colspan="3" class="text-right font-weight-bold" style="font-size: 1.2em;">TOTAL BAYAR</td>
                    <td class="text-right font-weight-bold" style="font-size: 1.2em;">{{ Helper::convertToRupiah($grand_total) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer Dihapus --}}
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Reservasi Rapat - {{ $customer->nama }} - {{ Helper::dateFormat($date) }}</title>
    {{-- Menggunakan Bootstrap CDN yang sama dengan contoh kamar --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { font-family: sans-serif; background: #fff; }
        .invoice-header { border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; }
        .logo { max-width: 300px; } /* Sesuaikan ukuran logo jika perlu */
        .invoice-title { float: right; color: #555; }
        .table th { background-color: #f8f9fa; }
        
        /* Watermark dihapus sesuai request */
    </style>
</head>
<body onload="window.print()"> {{-- Otomatis trigger print saat dibuka --}}

    {{-- Watermark dihapus --}}

    <div class="container mt-5">
        {{-- HEADER --}}
        <div class="row invoice-header">
            <div class="col-7">
                {{-- Logo Hotel Sawunggaling --}}
                <img src="{{ asset('img/logo-anda.png') }}" alt="Logo Hotel" class="logo">
                <br>
                {{-- Alamat Baru (Sama persis dengan invoice kamar) --}}
                <small>Jl. Sawunggaling No.13, Tamansari, Kec. Bandung Wetan,<br> 
                Kota Bandung, Jawa Barat | Telp. 081917044390</small>
            </div>
            <div class="col-5 text-right">
                <h2 class="invoice-title">INVOICE RESERVASI RUANG RAPAT</h2>
                <br><br>
                <strong>No: {{ $transactionCode }}</strong><br>
                Tanggal Cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </div>
        </div>

        {{-- INFO PELANGGAN --}}
        <div class="row mb-4">
            <div class="col-6">
                <strong>Nama Tamu/Instansi:</strong><br>
                <h4>{{ $customer->instansi ?? '-' }}</h4>
                <p>
                    {{ $customer->nama }}<br>
                    No. HP: {{ $customer->no_hp ?? '-' }}
                </p>
            </div>
            <div class="col-6 text-right">
                <strong>Detail Acara:</strong><br>
                Tanggal: {{ Helper::dateFormat($date) }}<br>
                Waktu: {{ $timeStart }} - {{ $timeEnd }} ({{ $duration }} Jam)<br>
                Peserta: {{ $pax }} Orang
            </div>
        </div>

        {{-- TABEL ITEM --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-center">Qty / Durasi</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                {{-- Item 1: Paket Rapat --}}
                <tr>
                    <td>
                        <strong>{{ $paket->name }}</strong><br>
                        <small class="text-muted">Paket Konsumsi/Fasilitas (Per Orang)</small>
                    </td>
                    <td class="text-center">{{ $pax }} Pax</td>
                    <td class="text-right">{{ Helper::convertToRupiah($paket->harga) }}</td>
                    <td class="text-right">{{ Helper::convertToRupiah($biayaPaketTotal) }}</td>
                </tr>

                {{-- Item 2: Sewa Ruangan --}}
                <tr>
                    <td>
                        <strong>Sewa Ruangan</strong><br>
                        <small class="text-muted">Biaya Pemakaian Ruang Rapat</small>
                    </td>
                    <td class="text-center">{{ $duration }} Jam</td>
                    <td class="text-right">Rp 100.000</td>
                    <td class="text-right">{{ Helper::convertToRupiah($biayaSewaRuangTotal) }}</td>
                </tr>
            </tbody>
            <tfoot>
                {{-- Subtotal --}}
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Subtotal</td>
                    <td class="text-right">{{ Helper::convertToRupiah($subTotal) }}</td>
                </tr>
                {{-- Pajak --}}
                <tr>
                    <td colspan="3" class="text-right font-weight-bold">Pajak PB1 (10%)</td>
                    <td class="text-right">{{ Helper::convertToRupiah($pajak) }}</td>
                </tr>
                {{-- Grand Total --}}
                <tr class="bg-light">
                    <td colspan="3" class="text-right font-weight-bold" style="font-size: 1.2em;">TOTAL BAYAR</td>
                    <td class="text-right font-weight-bold" style="font-size: 1.2em;">{{ Helper::convertToRupiah($grandTotal) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer dihapus sesuai request --}}
    </div>
</body>
</html>
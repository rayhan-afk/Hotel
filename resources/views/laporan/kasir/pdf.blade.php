<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Kasir</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #50200C;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #50200C;
            font-size: 18px;
            margin-bottom: 3px;
        }
        
        .header h2 {
            color: #50200C;
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 9px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 15px;
            background-color: #F7F3E4;
            padding: 8px;
            border-radius: 5px;
        }
        
        .info-section table {
            width: 100%;
        }
        
        .info-section td {
            padding: 2px 5px;
            font-size: 10px;
        }
        
        .info-section td:first-child {
            font-weight: bold;
            width: 100px;
            color: #50200C;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table.data-table thead {
            background-color: #50200C;
            color: white;
        }
        
        table.data-table th {
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }
        
        table.data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        
        table.data-table tbody tr:nth-child(even) {
            background-color: #F7F3E4;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            margin-top: 15px;
            background-color: #50200C;
            color: white;
            padding: 12px;
            border-radius: 5px;
        }
        
        .total-section table {
            width: 100%;
        }
        
        .total-section td {
            padding: 4px;
            font-size: 11px;
        }
        
        .total-section td:last-child {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-tunai {
            background-color: #28a745;
            color: white;
        }
        
        .badge-transfer {
            background-color: #007bff;
            color: white;
        }
        
        .menu-item {
            margin-bottom: 2px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>BOUTIQUE HOTEL SAWUNGGALING</h1>
        <h2>Laporan Transaksi Kasir (POS)</h2>
        <p>Jl. Bojongkoneng No.56A, Babakan, Kec. Bogor Tengah, Kota Bogor, Jawa Barat</p>
    </div>

    {{-- INFO PERIODE --}}
    <div class="info-section">
        <table>
            <tr>
                <td>Periode</td>
                <td>: {{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM Y') }}</td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ $printDate }}</td>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td>: {{ $totalTransaksi }} transaksi</td>
            </tr>
        </table>
    </div>

    {{-- TABEL DATA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 18%;">INVOICE</th>
                <th style="width: 15%;">WAKTU</th>
                <th style="width: 40%;">MENU TERJUAL</th>
                <th style="width: 10%;" class="text-center">METODE</th>
                <th style="width: 13%;" class="text-right">TOTAL (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $transaction->invoice_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->isoFormat('D MMM Y HH:mm') }}</td>
                    <td>
                        @foreach($transaction->details as $detail)
                            <div class="menu-item">
                                • {{ $detail->menu->name ?? 'Menu Terhapus' }} ({{ $detail->quantity }})
                            </div>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ strtolower($transaction->payment_method) }}">
                            {{ strtoupper($transaction->payment_method) }}
                        </span>
                    </td>
                    <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 25px;">
                        Tidak ada data transaksi pada periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL SECTION --}}
    <div class="total-section">
        <table>
            <tr>
                <td>TOTAL OMSET PERIODE INI:</td>
                <td>Rp {{ number_format($totalOmset, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis oleh sistem pada {{ $printDate }}</p>
        <p>&copy; {{ date('Y') }} Boutique Hotel Sawunggaling - Semua Hak Dilindungi</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pos</title>
</head>
<body>
    <center>
        <h3>LAPORAN TRANSAKSI POS</h3>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </center>

    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 50px; text-align: center;">No</th>
                <th style="width: 120px; text-align: center;">No Invoice</th>
                <th style="width: 100px; text-align: center;">Tanggal</th>
                <th style="width: 80px; text-align: center;">Jam</th>
                <th style="width: 150px; text-align: center;">Metode Pembayaran</th>
                <th style="width: 300px;">Menu Terjual (Qty)</th>
                <th style="width: 120px; text-align: right;">Total Tagihan</th>
                <th style="width: 120px; text-align: right;">Dibayar</th>
                <th style="width: 120px; text-align: right;">Kembalian</th>
            </tr>
        </thead>
        <tbody>
            @php $totalOmset = 0; @endphp
            @foreach($transactions as $index => $row)
                @php 
                    $totalOmset += $row->total_amount;
                    
                    // Format list menu
                    $itemList = $row->details->map(function($detail) {
                        $menuName = $detail->menu ? $detail->menu->name : 'Menu Terhapus';
                        return $menuName . ' (' . $detail->qty . ')';
                    })->implode(', ');
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $row->invoice_number }}</td>
                    <td style="text-align: center;">{{ $row->created_at->format('d-m-Y') }}</td>
                    <td style="text-align: center;">{{ $row->created_at->format('H:i') }}</td>
                    <td style="text-align: center;">{{ $row->payment_method ?? '-' }}</td>
                    <td>{{ $itemList }}</td>
                    <td style="text-align: right;">{{ number_format($row->total_amount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->pay_amount, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($row->change_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #e6e6e6;">
                <td colspan="6" style="text-align: right;">TOTAL OMSET</td>
                <td style="text-align: right;">{{ number_format($totalOmset, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
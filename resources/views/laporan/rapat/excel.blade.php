<!DOCTYPE html>
<html>
<head>
    <title>Laporan Ruang Rapat</title>
</head>
<body>
    <center>
        <h3>LAPORAN TRANSAKSI RUANG RAPAT</h3>
        {{-- Menampilkan periode tanggal --}}
        @if(request('start_date') && request('end_date'))
            <p>Periode: {{ date('d-m-Y', strtotime(request('start_date'))) }} s/d {{ date('d-m-Y', strtotime(request('end_date'))) }}</p>
        @else
            <p>Periode: Semua Waktu</p>
        @endif
    </center>

    <table border="1" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 40px; text-align: center;">No</th>
                <th style="width: 100px; text-align: center;">No Transaksi</th>
                <th style="width: 200px;">Instansi / Perusahaan</th>
                <th style="width: 150px;">Nama Pemesan</th>
                <th style="width: 120px;">No Handphone</th>
                <th style="width: 100px; text-align: center;">Tanggal Rapat</th>
                <th style="width: 80px; text-align: center;">Jam Mulai</th>
                <th style="width: 80px; text-align: center;">Jam Selesai</th>
                <th style="width: 80px; text-align: center;">Peserta</th>
                <th style="width: 100px; text-align: center;">Status Bayar</th>
                <th style="width: 100px; text-align: center;">Status Reservasi</th>
                <th style="width: 120px; text-align: right;">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($transactions as $index => $row)
                @php 
                    $grandTotal += $row->total_pembayaran; 
                    
                    // Ambil data customer (safety check pakai ??)
                    $instansi = $row->rapatCustomer->instansi ?? '-';
                    $nama = $row->rapatCustomer->nama ?? '-';
                    $hp = $row->rapatCustomer->no_hp ?? '-';
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">#{{ $row->id }}</td>
                    <td>{{ $instansi }}</td>
                    <td>{{ $nama }}</td>
                    
                    <td>'{{ $hp }}</td>
                    
                    <td style="text-align: center;">{{ date('d-m-Y', strtotime($row->tanggal_pemakaian)) }}</td>
                    <td style="text-align: center;">{{ $row->waktu_mulai }}</td>
                    <td style="text-align: center;">{{ $row->waktu_selesai }}</td>
                    <td style="text-align: center;">{{ $row->jumlah_peserta }}</td>
                    
                    <td style="text-align: center;">{{ $row->status_pembayaran }}</td>
                    <td style="text-align: center;">{{ $row->status_reservasi }}</td>
                    
                    <td style="text-align: right;">{{ number_format($row->total_pembayaran, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #e6e6e6;">
                <td colspan="11" style="text-align: right;">TOTAL PENDAPATAN</td>
                <td style="text-align: right;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
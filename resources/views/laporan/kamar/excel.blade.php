<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Kamar</title>
</head>
<body>
    <center>
        <h3>LAPORAN RIWAYAT KAMAR</h3>
        {{-- Menampilkan tanggal filter jika ada --}}
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
                <th style="width: 100px; text-align: center;">ID Transaksi</th>
                <th style="width: 150px;">Nama Tamu</th>
                <th style="width: 80px; text-align: center;">No. Kamar</th>
                <th style="width: 100px; text-align: center;">Tipe Kamar</th>
                
                <th style="width: 110px; text-align: center;">Check In</th>
                <th style="width: 110px; text-align: center;">Check Out</th>
                <th style="width: 80px; text-align: center;">Durasi (Malam)</th>
                <th style="width: 110px; text-align: center;">Rencana Check Out</th>
                
                <th style="width: 200px;">Catatan Waktu</th>
                
                <th style="width: 80px; text-align: center;">Sarapan</th>
                <th style="width: 120px; text-align: right;">Total Harga</th>
                <th style="width: 100px; text-align: center;">Status</th>
                
                <th style="width: 150px;">Email</th>
                <th style="width: 120px;">No HP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $row)
                @php
                    // --- 1. LOGIKA DURASI ---
                    $totalHarga = $row->total_price ?? 0;
                    $roomPrice = $row->room->price ?? 1;
                    if($roomPrice <= 0) $roomPrice = 1;
                    
                    $durasiPaket = round($totalHarga / $roomPrice);
                    if($durasiPaket < 1) $durasiPaket = 1;

                    // --- 2. LOGIKA WAKTU (EARLY/LATE) ---
                    $realCheckIn = \Carbon\Carbon::parse($row->check_in);
                    $realCheckOut = $row->check_out ? \Carbon\Carbon::parse($row->check_out) : null;
                    $planCheckOut = $realCheckIn->copy()->addDays($durasiPaket);

                    $notes = [];

                    // Early Check-in (Sebelum jam 14:00)
                    if ($realCheckIn->format('H') < 14) {
                        $notes[] = 'Early Check-in (' . $realCheckIn->format('H:i') . ')';
                    }

                    if ($realCheckOut) {
                        // Late Check-out (Setelah jam 12:00)
                        if ($realCheckOut->format('H') >= 12) {
                            $notes[] = 'Late Check-out (' . $realCheckOut->format('H:i') . ')';
                        }
                        // Pulang Awal
                        if ($realCheckOut->startOfDay()->lt($planCheckOut->copy()->startOfDay())) {
                            $notes[] = 'Pulang Lebih Awal';
                        }
                    } else {
                        $notes[] = 'Belum Checkout';
                    }
                    
                    $catatanWaktu = empty($notes) ? '-' : implode(', ', $notes);
                    
                    // Format HP
                    $hp = $row->customer->phone ?? '-';
                @endphp

                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">#{{ $row->id }}</td>
                    <td>{{ $row->customer->name ?? 'Guest' }}</td>
                    <td style="text-align: center;">{{ $row->room->number ?? '-' }}</td>
                    <td style="text-align: center;">{{ $row->room->type->name ?? '-' }}</td>
                    
                    <td style="text-align: center;">{{ $realCheckIn->format('d/m/Y H:i') }}</td>
                    <td style="text-align: center;">{{ $realCheckOut ? $realCheckOut->format('d/m/Y H:i') : '-' }}</td>
                    <td style="text-align: center;">{{ $durasiPaket }}</td>
                    <td style="text-align: center;">{{ $planCheckOut->format('d/m/Y') }}</td>
                    
                    <td style="color: {{ $catatanWaktu != '-' ? 'red' : 'black' }};">
                        {{ $catatanWaktu }}
                    </td>
                    
                    <td style="text-align: center;">{{ ($row->breakfast == 'Yes' || $row->breakfast == 1) ? 'Yes' : 'No' }}</td>
                    <td style="text-align: right;">{{ number_format($totalHarga, 0, ',', '.') }}</td>
                    <td style="text-align: center;">{{ $row->status }}</td>
                    
                    <td>{{ $row->customer->user->email ?? '-' }}</td>
                    <td>'{{ $hp }}</td> </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
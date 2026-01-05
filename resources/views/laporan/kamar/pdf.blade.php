<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Kamar</title>
    <style>
        /* Setup Halaman A4 Landscape */
        @page { margin: 15px; }
        
        body { font-family: sans-serif; font-size: 8pt; color: #333; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #50200C; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #50200C; font-size: 14pt; }
        .header h3 { margin: 2px 0; font-size: 10pt; }
        .header p { margin: 0; font-size: 8pt; color: #555; }

        /* Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #444; padding: 6px; vertical-align: top; }
        
        /* Styling Header Tabel */
        th { 
            background-color: #f7f3e8; /* Warna Cream */
            color: #50200C; 
            font-weight: bold; 
            text-align: center;
            font-size: 7.5pt;
        }

        /* Helper Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .small { font-size: 7pt; display: block; color: #555; }
        
        /* Styling menyerupai Badge di PDF */
        .badge-time {
            display: inline-block;
            background-color: #eee;
            border: 1px solid #ccc;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            margin-top: 2px;
        }
        
        /* Warna Teks Status */
        .note-early { color: #d35400; font-style: italic; font-weight: bold; font-size: 6.5pt; display: block; margin-top: 2px; }
        .note-late { color: #c0392b; font-style: italic; font-weight: bold; font-size: 6.5pt; display: block; margin-top: 2px; }
        .note-blue { color: #2980b9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
    <h2>HOTEL MANAGEMENT SYSTEM</h2>
    <h3>LAPORAN RIWAYAT TRANSAKSI KAMAR</h3>
    <p>
        Dicetak pada: {{ $date }} <br>
        
        {{-- LOGIKA PERIODE TANGGAL --}}
        Periode Data: 
        @if($start_date && $end_date)
            <span style="font-weight: bold; color: #000;">
                {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }} 
                s/d 
                {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}
            </span>
        @else
            <span style="font-weight: bold; color: #000;">Semua Waktu</span>
        @endif
    </p>
</div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="15%">Tamu & Kontak</th>
                <th width="10%">Kamar</th>
                <th width="15%">Paket Menginap (Rencana)</th>
                <th width="12%">Masuk (Real)</th>
                <th width="12%">Keluar (Real)</th>
                <th width="5%">Bfast</th>
                <th width="12%">Total (Rp)</th>
                <th width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
           @forelse($transactions as $trx)
    @php
        // -----------------------------------------------------------
        // 1. AMBIL DATA MENTAH (Bypass Accessor Model biar jam aman)
        // -----------------------------------------------------------
        $rawCheckIn  = $trx->getRawOriginal('check_in');
        $rawUpdated  = $trx->getRawOriginal('updated_at'); // INI KUNCINYA (Sesuai JS baris 94)
        $rawCheckOut = $trx->getRawOriginal('check_out');  // Ini hanya untuk Rencana/Plan

        // -----------------------------------------------------------
        // 2. LOGIKA MASUK (Sesuai JS Kolom 3)
        // -----------------------------------------------------------
        $realCheckIn = \Carbon\Carbon::parse($rawCheckIn);
        $isEarlyIn   = $realCheckIn->format('H') < 14;

        // -----------------------------------------------------------
        // 3. LOGIKA KELUAR REAL (Meniru JS Kolom 4)
        // -----------------------------------------------------------
        $status = $trx->status;
        
        // Cek apakah statusnya masih "Check In" atau "Reservation" (Belum keluar)
        $isStay = in_array($status, ['Check In', 'Reservation']);

        $realCheckOut = null;
        $isLateOut    = false;
        $isEarlyDate  = false; // Istilah JS: Early Check-out secara tanggal

        // Jika Tamu SUDAH KELUAR (Status: Done, Selesai, Payment Done, dll)
        if (!$isStay && $rawUpdated) {
            // A. Ambil Jam dari updated_at (Sesuai JS: let actualOut = moment(data))
            $realCheckOut = \Carbon\Carbon::parse($rawUpdated);

            // B. Bandingkan dengan Rencana (Sesuai JS: let planOut = moment(row.check_out))
            $planOut = \Carbon\Carbon::parse($rawCheckOut);

            // C. Logic Late Check-out (Sesuai JS: actualOut.hour() >= 12)
            $isLateOut = $realCheckOut->format('H') >= 12;

            // D. Logic Pulang Awal Tanggal (Sesuai JS: actualDateOnly.isBefore(planDateOnly))
            if ($realCheckOut->copy()->startOfDay()->lt($planOut->copy()->startOfDay())) {
                $isEarlyDate = true;
            }
        }

        // -----------------------------------------------------------
        // 4. DURASI PAKET (Sesuai JS Kolom 2)
        // -----------------------------------------------------------
        $planInDate  = \Carbon\Carbon::parse($rawCheckIn)->startOfDay();
        $planOutDate = \Carbon\Carbon::parse($rawCheckOut)->startOfDay();
        $durasiPaket = $planOutDate->diffInDays($planInDate);
        if($durasiPaket < 1) $durasiPaket = 1;

        // Harga
        $totalHarga = $trx->total_price ?? 0;
        $hp = $trx->customer->phone ?? '-';
    @endphp

    {{-- TAMPILAN TABEL --}}
    <tr>
        <td class="text-center">{{ $loop->iteration }}</td>
        
        {{-- 1. Tamu --}}
        <td>
            <span class="fw-bold">{{ $trx->customer->name ?? 'Guest' }}</span>
            <span class="small">{{ $trx->customer->user->email ?? '-' }}</span>
            <span class="small">HP: {{ $hp }}</span>
        </td>
        
        {{-- 2. Kamar --}}
        <td class="text-center">
            <span class="fw-bold" style="font-size: 9pt;">{{ $trx->room->number ?? '-' }}</span><br>
            <span class="small">{{ $trx->room->type->name ?? '' }}</span>
        </td>
        
        {{-- 3. Paket Rencana --}}
        <td>
            <div class="small">In: {{ $planInDate->format('d/m/Y') }}</div>
            <div class="fw-bold" style="font-size: 7.5pt;">Durasi: {{ $durasiPaket }} Malam</div>
            <div class="small" style="border-top: 1px dashed #ccc; margin-top: 2px; padding-top: 2px;">
                Out: {{ $planOutDate->format('d/m/Y') }}
            </div>
        </td>

        {{-- 4. Masuk Real --}}
        <td class="text-center">
            <div>{{ $realCheckIn->format('d/m/Y') }}</div>
            <span class="badge-time">
                {{ $realCheckIn->format('H:i') }}
            </span>
            @if($isEarlyIn)
                <span class="note-early">Early Check-in</span>
            @endif
        </td>
        
        {{-- 5. KELUAR REAL (Sesuai JS Kolom 4) --}}
        <td class="text-center">
            @if($isStay)
                {{-- Jika status masih Check In --}}
                <span class="note-blue">Belum Keluar</span>
            @elseif($realCheckOut)
                {{-- Tampilkan Tanggal & Jam dari updated_at --}}
                <div>{{ $realCheckOut->format('d/m/Y') }}</div>
                <span class="badge-time">
                    {{ $realCheckOut->format('H:i') }}
                </span>
                
                {{-- Badge Status Waktu --}}
                @if($isEarlyDate)
                    <span class="note-early">Early Check-out</span>
                @elseif($isLateOut)
                    <span class="note-late">Late Check-out</span>
                @endif
            @else
                -
            @endif
        </td>
        
        {{-- 6. Bfast --}}
        <td class="text-center">
            {{ ($trx->breakfast == 'Yes' || $trx->breakfast == 1) ? 'Ya' : 'Tdk' }}
        </td>
        
        {{-- 7. Harga --}}
        <td class="text-right">
            {{ number_format($totalHarga, 0, ',', '.') }}
        </td>
        
        {{-- 8. Status --}}
        <td class="text-center">
            {{ $trx->status }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center" style="padding: 20px;">
            Tidak ada data transaksi.
        </td>
    </tr>
@endforelse
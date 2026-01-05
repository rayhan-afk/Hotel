<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stock Opname Amenities</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; color: #5c3a21; }
        .header p { margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background-color: #5c3a21; color: white; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-red { color: red; font-weight: bold; }
        .text-green { color: green; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN STOCK OPNAME AMENITIES</h2>
        <p>Periode: {{ date('d M Y', strtotime($startDate)) }} s/d {{ date('d M Y', strtotime($endDate)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 25%">Nama Amenities</th>
                <th style="width: 10%" class="text-center">Sistem</th>
                <th style="width: 10%" class="text-center">Fisik</th>
                <th style="width: 10%" class="text-center">Selisih</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                
                {{-- PERBAIKAN 1: Menggunakan 'nama_barang' sesuai JSON --}}
                <td>{{ $row->amenity->nama_barang ?? 'Item Dihapus' }}</td>
                
                {{-- PERBAIKAN 2: Menggunakan nama kolom bahasa Inggris sesuai JSON --}}
                <td class="text-center">{{ round($row->system_stock) }}</td>
                <td class="text-center">{{ round($row->physical_stock) }}</td>
                
                <td class="text-center">
                    {{-- PERBAIKAN 3: Menggunakan 'difference' --}}
                    @php $selisih = $row->difference; @endphp
                    <span class="{{ $selisih < 0 ? 'text-red' : ($selisih > 0 ? 'text-green' : '') }}">
                        {{ $selisih > 0 ? '+' : '' }}{{ round($selisih) }}
                    </span>
                </td>
                
                {{-- PERBAIKAN 4: Menggunakan 'notes' --}}
                <td>{{ $row->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data stock opname pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
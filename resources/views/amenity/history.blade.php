<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="bg-light">
            <tr>
                <th>Tanggal</th>
                <th>Nama Amenities</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $history)
                <tr>
                    <td>{{ $history->created_at->format('d M Y, H:i') }}</td>
                    
                    {{-- Pastikan relasi 'amenity' dan kolom nama (misal: name atau nama_amenities) benar --}}
                   <td>{{ $history->amenity->nama_barang ?? 'Item Dihapus' }}</td>
                    
                    <td>{{ (float)$history->system_stock }}</td>
                    <td>{{ (float)$history->physical_stock }}</td>
                    
                    <td class="fw-bold {{ $history->difference < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $history->difference > 0 ? '+' : '' }}{{ (float)$history->difference }}
                    </td>
                    
                    <td class="text-muted small">{{ $history->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada riwayat stock opname.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- PENTING: File ini TIDAK BOLEH ada @extends atau @section --}}
{{-- Karena file ini hanya potongan HTML yang akan dimasukkan ke dalam Modal --}}

<div class="p-2">
    {{-- BAGIAN 1: INFO HEADER --}}
    <div class="card mb-4 border-left-primary shadow-sm h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Diajukan Oleh
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ $approval->requester->name ?? 'User Tidak Dikenal' }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> {{ $approval->created_at->isoFormat('D MMMM Y HH:mm') }}
                    </div>
                </div>
                <div class="col-auto">
                    @if($approval->status == 'pending')
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fas fa-clock"></i> Pending</span>
                    @elseif($approval->status == 'approved')
                        <span class="badge bg-success text-dark px-3 py-2 fs-6"><i class="fas fa-check"></i> Approved</span>
                    @else
                        <span class="badge bg-danger text-dark px-3 py-2 fs-6"><i class="fas fa-times"></i> Rejected</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN 2: TABEL PERBANDINGAN --}}
    <h5 class="mb-3 fw-bold text-dark border-bottom pb-2">
        <i class="fas fa-exchange-alt me-2 text-primary" style="color: #8B4513;"></i>Detail Perubahan
    </h5>
        
    <div class="table-responsive border rounded">
        <table class="table table-bordered table-striped mb-0 align-middle">
            <thead class="bg-light">
                <tr class="text-center small text-uppercase">
                    <th style="width: 25%;">Kolom</th>
                    
                   {{-- [FIX WARNA] Background Merah Cerah (#ffcccc) + Teks Hitam --}}
                    <th style="width: 37%; background-color: #ffcccc;" class="text-dark fw-bold">
                        <i class="fas fa-history me-1"></i> Data Lama
                    </th>
                    
                    {{-- [FIX WARNA] Background Hijau Cerah (#ccffcc) + Teks Hitam --}}
                    <th style="width: 37%; background-color: #ccffcc;" class="text-dark fw-bold">
                        <i class="fas fa-file-import me-1"></i> Data Baru
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Helper Function untuk Decode Data dengan Aman
                    function safeDecode($data) {
                        if (is_array($data)) return $data;
                        $decoded = json_decode($data, true);
                        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
                    }

                    $oldData = safeDecode($approval->old_data);
                    $newData = safeDecode($approval->new_data);

                    // Gabung semua key, hapus duplikat
                    $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
                    
                    // Daftar kolom yang TIDAK perlu ditampilkan (Blacklist)
                    $ignoredKeys = [
                        'id', 'created_at', 'updated_at', 'deleted_at', 
                        'image', 'main_image_path', 'remember_token', 'password',
                        'email_verified_at', 'requester_id', 'requested_by'
                    ]; 
                    
                    $hasChanges = false;
                @endphp

                @foreach($allKeys as $key)
                    @if(in_array($key, $ignoredKeys)) @continue @endif

                    @php
                        $oldVal = $oldData[$key] ?? '-';
                        $newVal = $newData[$key] ?? '-';
                        
                        // Cek perubahan (String comparison)
                        $valOldStr = is_array($oldVal) ? json_encode($oldVal) : (string)$oldVal;
                        $valNewStr = is_array($newVal) ? json_encode($newVal) : (string)$newVal;
                        
                        $isChanged = $valOldStr !== $valNewStr;
                        if($isChanged) $hasChanges = true;
                        
                        // Format Rupiah (Lebih Aman)
                        if(str_contains(strtolower($key), 'price') || str_contains(strtolower($key), 'harga')) {
                            if(is_numeric($oldVal)) $oldVal = 'Rp ' . number_format($oldVal, 0, ',', '.');
                            if(is_numeric($newVal)) $newVal = 'Rp ' . number_format($newVal, 0, ',', '.');
                        }
                    @endphp

                    <tr>
                        {{-- NAMA KOLOM --}}
                        <td class="fw-bold bg-white text-capitalize">
                            {{ str_replace(['_', '-'], ' ', $key) }}
                        </td>
                        
                        {{-- DATA LAMA --}}
                        <td class="{{ $isChanged ? 'text-dark' : 'text-muted' }}" 
                            style="{{ $isChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                            @if($isChanged) 
                                <del class="small opacity-75">
                                    {{ is_array($oldVal) ? json_encode($oldVal, JSON_PRETTY_PRINT) : $oldVal }}
                                </del>
                            @else 
                                {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                            @endif
                        </td>

                        {{-- DATA BARU --}}
                        <td class="{{ $isChanged ? 'fw-bold text-dark' : '' }}" 
                            style="{{ $isChanged ? 'background-color: #fff3cd; border-left: 4px solid #ffc107;' : '' }}">
                            
                            {{ is_array($newVal) ? json_encode($newVal, JSON_PRETTY_PRINT) : $newVal }}
                            
                            @if($isChanged)
                                <i class="fas fa-check-circle text-success float-end mt-1"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if(empty($allKeys) || !$hasChanges)
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                            <em>Tidak ada perubahan data teks yang terdeteksi.<br>(Kemungkinan hanya perubahan gambar/file)</em>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- BAGIAN 3: INFO APPROVER --}}
    @if($approval->status != 'pending')
        <div class="mt-4 p-3 rounded shadow-sm border {{ $approval->status == 'approved' ? 'border-success bg-white' : 'border-danger bg-white' }}">
            <h6 class="fw-bold mb-2 {{ $approval->status == 'approved' ? 'text-success' : 'text-danger' }}">
                <i class="fas {{ $approval->status == 'approved' ? 'fa-check-circle' : 'fa-times-circle' }} me-2"></i>
                Keputusan: {{ ucfirst($approval->status) }}
            </h6>
            <div class="d-flex justify-content-between text-muted small">
                <span>Oleh: <strong>{{ $approval->approver->name ?? 'System' }}</strong></span>
                <span><i class="far fa-clock me-1"></i> {{ $approval->approved_at ? \Carbon\Carbon::parse($approval->approved_at)->format('d M Y H:i') : '-' }}</span>
            </div>
            @if($approval->notes)
                <div class="mt-2 p-2 bg-light rounded border-start border-3 {{ $approval->status == 'approved' ? 'border-success' : 'border-danger' }}">
                    <small class="fw-bold d-block text-secondary">Catatan:</small>
                    <span class="fst-italic text-dark">"{{ $approval->notes }}"</span>
                </div>
            @endif
        </div>
    @endif
</div>
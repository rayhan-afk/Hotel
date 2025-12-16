{{-- PENTING: File ini TIDAK BOLEH ada @extends atau @section --}}
{{-- Karena file ini hanya potongan HTML yang akan dimasukkan ke dalam Modal --}}

<div class="p-2" style="background-color: #F7F3E4;">
    {{-- BAGIAN 1: INFO HEADER --}}
    <div class="card mb-4 border-left-primary shadow-sm h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold mb-1" style="color: #50200C">
                        Diajukan Oleh
                    </div>
                    <div class="h5 mb-0 font-weight-bold" style="color: #50200C">
                        {{ $approval->requester->name ?? 'User Tidak Dikenal' }}
                    </div>
                    <div class="small mt-1" style="color: #50200C">
                        <i class="fas fa-calendar-alt me-1"></i> {{ $approval->created_at->isoFormat('D MMMM Y HH:mm') }}
                    </div>
                </div>
                <div class="col-auto">
                    @if($approval->status == 'pending')
                        <span class="badge badge-pending px-3 py-2 fs-6"><i class="fas fa-clock"></i> Pending</span>
                    @elseif($approval->status == 'approved')
                        <span class="badge badge-approved px-3 py-2 fs-6"><i class="fas fa-check"></i> Approved</span>
                    @else
                        <span class="badge badge-rejected px-3 py-2 fs-6"><i class="fas fa-times"></i> Rejected</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- BAGIAN 2: TABEL PERBANDINGAN --}}
    <h5 class="mb-3 fw-bold border-bottom pb-2" style="color: #50200C">
        <i class="fas fa-exchange-alt me-2" style="color: #50200C;"></i>Detail Perubahan
    </h5>
        
    <div class="table-responsive border rounded">
        <table class="table table-bordered table-striped mb-0 align-middle">
            <thead class=" " style="background-color: #F7F3E4">
                <tr class="text-center small" style="color: #50200C">
                    <th style="width: 25%;">Kolom</th>
                    <th style="width: 37%; background-color: #F2C2B8; color: #50200C" class="fw-bold">
                        <i class="fas fa-history me-1"></i> Data Lama
                    </th>
                    <th style="width: 37%; background-color: #A8D5BA;" class="fw-bold">
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
                    
                    // ✅ PERBAIKAN 1: Hapus 'main_image_path' dari daftar ignore
                    $ignoredKeys = [
                        'id', 'created_at', 'updated_at', 'deleted_at', 
                        'image', // Ignore 'image' karena ini objek file upload, kita pakai 'main_image_path'
                        // 'main_image_path', <--- INI DIHAPUS AGAR MUNCUL DI TABEL
                        'remember_token', 
                        'password',
                        'email_verified_at', 
                        'requester_id', 
                        'requested_by'
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
                    
                    // Format Rupiah
                    if(str_contains(strtolower($key), 'price') || str_contains(strtolower($key), 'harga')) {
                        if(is_numeric($oldVal)) $oldVal = 'Rp ' . number_format($oldVal, 0, ',', '.');
                        if(is_numeric($newVal)) $newVal = 'Rp ' . number_format($newVal, 0, ',', '.');
                    }

                    // Mapping Nama Kolom
                    $columnLabels = [
                        'type_id' => 'Tipe Kamar',
                        'number' => 'Nomor Kamar',
                        'capacity' => 'Kapasitas',
                        'price' => 'Harga',
                        'name' => 'Nama Kamar',
                        'status' => 'Status',
                        'area_sqm' => 'Luas Area (m²)',
                        'breakfast' => 'Sarapan',
                        'room_facilities' => 'Fasilitas Kamar',
                        'bathroom_facilities' => 'Fasilitas Kamar Mandi',
                        'main_image_path' => 'Gambar Kamar', // Label
                    ];
                    
                    $columnLabel = $columnLabels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

                    // ✅ HELPER PATH GAMBAR: Logic ini menyesuaikan folder repository Anda
                    // public/img/room/{ID}-{SLUG}/{FILENAME}
                    $getImageUrl = function($filename) use ($approval, $oldData) {
                        if ($approval->type === 'room') {
                            // Ambil nama dari data lama untuk memastikan slug folder benar (karena folder belum direname)
                            $slug = \Illuminate\Support\Str::slug($oldData['name'] ?? 'room');
                            return asset("img/room/{$approval->reference_id}-{$slug}/{$filename}");
                        }
                        // Fallback jika bukan room
                        return asset('storage/' . $filename);
                    };
                @endphp

                <tr>
                    <td class="fw-bold bg-white" style="color: #50200C;">
                        {{ $columnLabel }}
                    </td>
                    
                    {{-- ✅ PERBAIKAN 2: CEK KOLOM main_image_path --}}
                    @if($key === 'main_image_path')
                        {{-- DATA LAMA - IMAGE --}}
                        <td class="{{ $isChanged ? 'text-dark' : 'text-muted' }}" 
                            style="color:#50200C !important; {{ $isChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                            @if($oldVal !== '-' && $oldVal !== null)
                                <div class="d-flex flex-column">
                                    <span class="small mb-2 text-break">{{ basename($oldVal) }}</span>
                                    
                                    <div class="border {{ $isChanged ? 'border-danger' : 'border-secondary' }} rounded p-2" style="max-width: 150px;">
                                        {{-- Panggil Helper URL --}}
                                        <img src="{{ $getImageUrl($oldVal) }}" 
                                             alt="Gambar Lama" 
                                             class="img-fluid rounded"
                                             style="cursor: pointer; max-height: 120px; object-fit: cover;"
                                             onclick="window.open(this.src, '_blank')"
                                             onerror="this.src='{{ asset('img/default/default-room.png') }}'">
                                    </div>
                                    <small class="text-muted mt-1"><i class="fas fa-search-plus me-1"></i>Klik zoom</small>
                                </div>
                            @else
                                <span class="text-muted small">Tidak ada gambar</span>
                            @endif
                        </td>

                        {{-- DATA BARU - IMAGE --}}
                        <td class="{{ $isChanged ? 'fw-bold text-dark' : '' }}" 
                            style="color:#50200C !important; {{ $isChanged ? 'background-color: #fff3cd; border-left: 4px solid #ffc107;' : '' }}; position: relative;">
                            @if($newVal !== '-' && $newVal !== null)
                                <div class="d-flex flex-column">
                                    <strong class="mb-2 text-break text-success">
                                        {{ basename($newVal) }}
                                    </strong>
                                    <div class="border border-success rounded p-2" style="max-width: 150px;">
                                        {{-- Panggil Helper URL --}}
                                        <img src="{{ $getImageUrl($newVal) }}" 
                                             alt="Gambar Baru" 
                                             class="img-fluid rounded"
                                             style="cursor: pointer; max-height: 120px; object-fit: cover;"
                                             onclick="window.open(this.src, '_blank')"
                                             onerror="this.src='{{ asset('img/default/default-room.png') }}'">
                                    </div>
                                    <small class="text-muted mt-1"><i class="fas fa-search-plus me-1"></i>Klik zoom</small>
                                </div>
                                @if($isChanged)
                                    <i class="fas fa-check-circle text-success position-absolute top-0 end-0 m-2"></i>
                                @endif
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    @else
                        {{-- DATA LAMA - TEXT BIASA --}}
                        <td class="{{ $isChanged ? 'text-dark' : 'text-muted' }}" 
                            style="color:#50200C !important; {{ $isChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                            @if($isChanged) 
                                <del class="small opacity-75">
                                    {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                                </del>
                            @else 
                                {{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}
                            @endif
                        </td>

                        {{-- DATA BARU - TEXT BIASA --}}
                        <td class="{{ $isChanged ? 'fw-bold text-dark' : '' }}" 
                            style="color:#50200C !important; {{ $isChanged ? 'background-color: #fff3cd; border-left: 4px solid #ffc107;' : '' }}">
                            
                            {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                            
                            @if($isChanged)
                                <i class="fas fa-check-circle text-success float-end mt-1"></i>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach

                @if(empty($allKeys) || !$hasChanges)
                    <tr>
                        <td colspan="3" class="text-center py-4" style="color: #50200C">
                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                            <em>Tidak ada perubahan data yang terdeteksi.</em>
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
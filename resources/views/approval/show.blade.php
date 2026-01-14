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

    {{-- BAGIAN 2: DETAIL PERUBAHAN --}}
    <h5 class="mb-3 fw-bold border-bottom pb-2" style="color: #50200C">
        <i class="fas fa-exchange-alt me-2" style="color: #50200C;"></i>Detail Perubahan
    </h5>

    {{-- ============================================ --}}
    {{-- KHUSUS UNTUK TYPE_PRICE (Perubahan Harga) --}}
    {{-- ============================================ --}}
    @if($approval->type === 'type_price')
        @php
            $oldPrices = is_array($approval->old_data) ? $approval->old_data : json_decode($approval->old_data, true);
            $newPrices = is_array($approval->new_data) ? $approval->new_data : json_decode($approval->new_data, true);
            $typeModel = \App\Models\Type::find($approval->reference_id);
        @endphp

        <div class="alert alert-info mb-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Tipe Kamar:</strong> {{ $typeModel ? $typeModel->name : 'Tidak Ditemukan' }}
        </div>

        <div class="table-responsive border rounded">
            <table class="table table-bordered table-striped mb-0 align-middle">
                <thead style="background-color: #F7F3E4">
                    <tr class="text-center small" style="color: #50200C">
                        <th style="width: 20%;">Customer Group</th>
                        <th style="width: 20%; background-color: #F2C2B8; color: #50200C" class="fw-bold">
                            <i class="fas fa-calendar-day me-1"></i> Weekday (Lama)
                        </th>
                        <th style="width: 20%; background-color: #A8D5BA;" class="fw-bold">
                            <i class="fas fa-calendar-day me-1"></i> Weekday (Baru)
                        </th>
                        <th style="width: 20%; background-color: #F2C2B8; color: #50200C" class="fw-bold">
                            <i class="fas fa-calendar-week me-1"></i> Weekend (Lama)
                        </th>
                        <th style="width: 20%; background-color: #A8D5BA;" class="fw-bold">
                            <i class="fas fa-calendar-week me-1"></i> Weekend (Baru)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($newPrices as $group => $prices)
                        @php
                            $oldWeekday = $oldPrices[$group]['weekday'] ?? 0;
                            $newWeekday = $prices['weekday'] ?? 0;
                            $oldWeekend = $oldPrices[$group]['weekend'] ?? 0;
                            $newWeekend = $prices['weekend'] ?? 0;
                            
                            $weekdayChanged = $oldWeekday != $newWeekday;
                            $weekendChanged = $oldWeekend != $newWeekend;
                        @endphp
                        <tr>
                            <td class="fw-bold" style="color: #50200C;">
                                <i class="fas fa-users me-2"></i>{{ $group }}
                            </td>
                            
                            {{-- Weekday Lama --}}
                            <td class="text-end {{ $weekdayChanged ? 'text-dark' : 'text-muted' }}" 
                                style="{{ $weekdayChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                                @if($weekdayChanged)
                                    <del class="small opacity-75">
                                        Rp {{ number_format($oldWeekday, 0, ',', '.') }}
                                    </del>
                                @else
                                    Rp {{ number_format($oldWeekday, 0, ',', '.') }}
                                @endif
                            </td>
                            
                            {{-- Weekday Baru --}}
                            <td class="text-end fw-bold {{ $weekdayChanged ? 'text-success' : '' }}" 
                                style="{{ $weekdayChanged ? 'background-color: #fff3cd; border-left: 4px solid #ffc107;' : '' }}">
                                Rp {{ number_format($newWeekday, 0, ',', '.') }}
                                @if($weekdayChanged)
                                    <i class="fas fa-arrow-up text-success ms-2"></i>
                                @endif
                            </td>
                            
                            {{-- Weekend Lama --}}
                            <td class="text-end {{ $weekendChanged ? 'text-dark' : 'text-muted' }}" 
                                style="{{ $weekendChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                                @if($weekendChanged)
                                    <del class="small opacity-75">
                                        Rp {{ number_format($oldWeekend, 0, ',', '.') }}
                                    </del>
                                @else
                                    Rp {{ number_format($oldWeekend, 0, ',', '.') }}
                                @endif
                            </td>
                            
                            {{-- Weekend Baru --}}
                            <td class="text-end fw-bold {{ $weekendChanged ? 'text-success' : '' }}" 
                                style="{{ $weekendChanged ? 'background-color: #fff3cd; border-left: 4px solid #ffc107;' : '' }}">
                                Rp {{ number_format($newWeekend, 0, ',', '.') }}
                                @if($weekendChanged)
                                    <i class="fas fa-arrow-up text-success ms-2"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    {{-- ============================================ --}}
    {{-- UNTUK TYPE LAINNYA (Data Biasa) --}}
    {{-- ============================================ --}}
    @else
        <div class="table-responsive border rounded">
            <table class="table table-bordered table-striped mb-0 align-middle">
                <thead class="" style="background-color: #F7F3E4">
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
                        
                        $ignoredKeys = [
                            'id', 'created_at', 'updated_at', 'deleted_at', 
                            'image',
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
                                'name' => 'Nama',
                                'information' => 'Informasi',
                                'status' => 'Status',
                                'area_sqm' => 'Luas Area (m²)',
                                'breakfast' => 'Sarapan',
                                'room_facilities' => 'Fasilitas Kamar',
                                'bathroom_facilities' => 'Fasilitas Kamar Mandi',
                                'main_image_path' => 'Gambar Kamar',
                            ];
                            
                            $columnLabel = $columnLabels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $key));

                            // Helper untuk Path Gambar
                            $getImageUrl = function($filename) use ($approval, $oldData) {
                                if ($approval->type === 'room') {
                                    $slug = \Illuminate\Support\Str::slug($oldData['name'] ?? 'room');
                                    return asset("img/room/{$approval->reference_id}-{$slug}/{$filename}");
                                }
                                return asset('storage/' . $filename);
                            };
                        @endphp

                        <tr>
                            <td class="fw-bold bg-white" style="color: #50200C;">
                                {{ $columnLabel }}
                            </td>
                            
                            {{-- CEK KOLOM main_image_path --}}
                            @if($key === 'main_image_path')
                                {{-- DATA LAMA - IMAGE --}}
                                <td class="{{ $isChanged ? 'text-dark' : 'text-muted' }}" 
                                    style="color:#50200C !important; {{ $isChanged ? 'background-color: #f9b9b9ff;' : '' }}">
                                    @if($oldVal !== '-' && $oldVal !== null)
                                        <div class="d-flex flex-column">
                                            <span class="small mb-2 text-break">{{ basename($oldVal) }}</span>
                                            
                                            <div class="border {{ $isChanged ? 'border-danger' : 'border-secondary' }} rounded p-2" style="max-width: 150px;">
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
    @endif

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
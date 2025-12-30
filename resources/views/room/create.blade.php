@php
    // DEFENSE CODE: Ensure $room is always defined (null for Create mode)
    $room = $room ?? null; 
@endphp

{{-- Form is used for both Create (room=null) and Edit (room=object) --}}
{{-- IMPORTANT: enctype="multipart/form-data" is required for file uploads --}}
<form id="form-save-room" class="row g-3" method="POST" action="{{ $room ? route('room.update', $room->id) : route('room.store') }}" enctype="multipart/form-data">
    @if($room)
        @method('PUT')
    @endif
    @csrf
    
    {{-- Room Type --}}
    <div class="col-md-6">
        <label for="type_id" class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
        <select id="type_id" name="type_id" class="form-control select2" required>
            <option value="" disabled selected>Pilih Tipe</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" {{ (old('type_id', $room?->type_id) == $type->id) ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        <div id="error_type_id" class="text-danger error"></div>
    </div>

    {{-- Room Number --}}
    <div class="col-md-6">
        <label for="number" class="form-label">Nomor Kamar <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="number" name="number" 
            value="{{ old('number', $room?->number) }}" placeholder="Contoh: 101" required>
        <div id="error_number" class="text-danger error"></div>
    </div>

    {{-- Room Name --}}
    <div class="col-md-12">
        <label for="name" class="form-label">Nama Kamar <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" 
            value="{{ old('name', $room?->name) }}" placeholder="Contoh: Deluxe Ocean View" required>
        <div id="error_name" class="text-danger error"></div>
    </div>

    {{-- Price --}}
    <div class="col-md-6">
        <label for="price" class="form-label">Harga per Malam (Rp) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="price" name="price" 
            value="{{ old('price', $room?->price) }}" placeholder="Contoh: 500000" required>
        <div id="error_price" class="text-danger error"></div>
    </div>

    {{-- Capacity --}}
    <div class="col-md-3">
        <label for="capacity" class="form-label">Kapasitas (Orang) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="capacity" name="capacity" 
            value="{{ old('capacity', $room?->capacity) }}" placeholder="Contoh: 2" required>
        <div id="error_capacity" class="text-danger error"></div>
    </div>

    {{-- Area Size --}}
    <div class="col-md-3">
        <label for="area_sqm" class="form-label">Luas (m²)</label>
        <input type="number" step="0.1" class="form-control" id="area_sqm" name="area_sqm" 
            value="{{ old('area_sqm', $room?->area_sqm) }}" placeholder="Contoh: 24.5">
        <div id="error_area_sqm" class="text-danger error"></div>
    </div>

    {{-- Room Facilities (Static Assets) --}}
    <div class="col-md-12">
        <label for="room_facilities" class="form-label">Fasilitas Kamar (Asset Tetap)</label>
        <textarea class="form-control" id="room_facilities" name="room_facilities" rows="3" 
            placeholder="Contoh: AC, TV, WiFi, Mini Bar">{{ old('room_facilities', $room?->room_facilities) }}</textarea>
        <small class="text-muted">Masukkan fasilitas elektronik/furniture.</small>
        <div id="error_room_facilities" class="text-danger error"></div>
    </div>

    {{-- Bathroom Facilities --}}
    <div class="col-md-12">
        <label for="bathroom_facilities" class="form-label">Fasilitas Kamar Mandi</label>
        <textarea class="form-control" id="bathroom_facilities" name="bathroom_facilities" rows="2" 
            placeholder="Contoh: Shower Air Panas, Bathtub">{{ old('bathroom_facilities', $room?->bathroom_facilities) }}</textarea>
        <div id="error_bathroom_facilities" class="text-danger error"></div>
    </div>

    {{-- [NEW] Amenities Section (Consumables) --}}
    <div class="col-md-12 mt-4">
        <div class="card border shadow-sm">
            <div class="card-header bg-light fw-bold">
                <i class="fas fa-box-open me-1"></i> Setup Amenities & Jatah Barang (Logistik)
                <small class="d-block text-muted fw-normal mt-1">Pilih barang habis pakai (Consumable) yang disediakan untuk kamar ini. Stok akan berkurang otomatis saat Check-In.</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 table-sm">
                        <thead class="table-secondary text-center">
                            <tr>
                                <th width="5%" class="align-middle">Pilih</th>
                                <th class="align-middle text-start">Nama Barang</th>
                                <th width="20%" class="align-middle">Jatah Per Kamar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($amenities) && $amenities->count() > 0)
                                @foreach ($amenities as $amenity)
                                    {{-- Check if room already has this amenity (for edit mode) --}}
                                    @php
                                        $isChecked = '';
                                        $currentAmount = 1;

                                        // In Edit mode, check the pivot relationship
                                        if($room && $room->amenities->isNotEmpty()) {
                                            $existingAmenity = $room->amenities->find($amenity->id);
                                            if($existingAmenity) {
                                                $isChecked = 'checked';
                                                $currentAmount = $existingAmenity->pivot->amount;
                                            }
                                        }
                                    @endphp

                                    <tr>
                                        <td class="text-center align-middle">
                                            {{-- Checkbox: name="amenities[]" --}}
                                            <div class="form-check d-flex justify-content-center">
                                                <input class="form-check-input" type="checkbox" 
                                                    name="amenities[]" 
                                                    value="{{ $amenity->id }}" 
                                                    id="amenity_{{ $amenity->id }}"
                                                    {{ $isChecked }}>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <label class="form-check-label w-100 cursor-pointer user-select-none" for="amenity_{{ $amenity->id }}">
                                                {{ $amenity->nama_barang }} 
                                                <span class="badge bg-secondary ms-1" style="font-size: 0.7em">{{ $amenity->satuan }}</span>
                                            </label>
                                        </td>
                                        <td class="align-middle">
                                            {{-- Input Quantity: name="amounts[ID]" --}}
                                            <input type="number" 
                                                class="form-control form-control-sm text-center" 
                                                name="amounts[{{ $amenity->id }}]" 
                                                value="{{ $currentAmount }}" 
                                                min="1"
                                                placeholder="Qty">
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Belum ada data Amenities (Non-Literan). Silakan tambahkan di menu Amenities.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Image (Using File Input) --}}
    <div class="col-md-12 mt-3">
        <label for="image" class="form-label">Upload Gambar Kamar</label>
        
        {{-- File Input --}}
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
        <div id="error_image" class="text-danger error"></div>
        <small class="text-muted">Format: JPG, PNG. Maks: 2MB.</small>

        {{-- Preview Old Image (Only in Edit mode if image exists) --}}
        @if($room)
            {{-- Assuming room model has a method or accessor for image URL --}}
            @php
                $imageUrl = method_exists($room, 'getImage') ? $room->getImage() : asset($room->main_image_path ?? 'img/default-room.jpg');
            @endphp
            
            @if($room->main_image_path)
                <div class="mt-3 p-2 border rounded bg-light text-center">
                    <small class="text-muted d-block mb-2">Gambar Saat Ini:</small>
                    <img src="{{ $imageUrl }}" 
                         alt="Room Image" 
                         class="img-thumbnail shadow-sm" 
                         style="max-height: 200px; object-fit: cover;">
                </div>
            @endif
        @endif
    </div>
</form>
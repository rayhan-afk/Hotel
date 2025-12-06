<form id="form-save-room" class="row g-3" method="POST" action="{{ isset($room) ? route('room.update', $room->id) : route('room.store') }}">
    @if(isset($room))
        @method('PUT')
    @endif
    @csrf
    
    {{-- Tipe Kamar --}}
    <div class="col-md-6">
        <label for="type_id" class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
        <select id="type_id" name="type_id" class="form-control select2" required>
            <option value="" disabled selected>Pilih Tipe</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" {{ (isset($room) && $room->type_id == $type->id) ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        <div id="error_type_id" class="text-danger error"></div>
    </div>

    {{-- Nomor Kamar --}}
    <div class="col-md-6">
        <label for="number" class="form-label">Nomor Kamar <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="number" name="number" 
            value="{{ $room->number ?? '' }}" placeholder="Contoh: 101" required>
        <div id="error_number" class="text-danger error"></div>
    </div>

    {{-- Nama Kamar (BARU) --}}
    <div class="col-md-12">
        <label for="name" class="form-label">Nama Kamar <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name" 
            value="{{ $room->name ?? '' }}" placeholder="Contoh: Deluxe Ocean View" required>
        <div id="error_name" class="text-danger error"></div>
    </div>

    {{-- Harga --}}
    <div class="col-md-6">
        <label for="price" class="form-label">Harga per Malam (Rp) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="price" name="price" 
            value="{{ $room->price ?? '' }}" placeholder="Contoh: 500000" required>
        <div id="error_price" class="text-danger error"></div>
    </div>

    {{-- Kapasitas --}}
    <div class="col-md-3">
        <label for="capacity" class="form-label">Kapasitas (Orang) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="capacity" name="capacity" 
            value="{{ $room->capacity ?? '' }}" placeholder="Contoh: 2" required>
        <div id="error_capacity" class="text-danger error"></div>
    </div>

    {{-- Luas Kamar (BARU) --}}
    <div class="col-md-3">
        <label for="area_sqm" class="form-label">Luas (m²)</label>
        <input type="number" step="0.1" class="form-control" id="area_sqm" name="area_sqm" 
            value="{{ $room->area_sqm ?? '' }}" placeholder="Contoh: 24.5">
        <div id="error_area_sqm" class="text-danger error"></div>
    </div>

    {{-- Fasilitas Kamar (BARU - Textarea menggantikan 'view') --}}
    <div class="col-md-12">
        <label for="room_facilities" class="form-label">Fasilitas Kamar</label>
        <textarea class="form-control" id="room_facilities" name="room_facilities" rows="3" 
            placeholder="Contoh: AC, TV, WiFi, Mini Bar">{{ $room->room_facilities ?? '' }}</textarea>
        <div id="error_room_facilities" class="text-danger error"></div>
    </div>

    {{-- Fasilitas Kamar Mandi (BARU) --}}
    <div class="col-md-12">
        <label for="bathroom_facilities" class="form-label">Fasilitas Kamar Mandi</label>
        <textarea class="form-control" id="bathroom_facilities" name="bathroom_facilities" rows="2" 
            placeholder="Contoh: Shower Air Panas, Bathtub, Handuk, Sabun & Sampo">{{ $room->bathroom_facilities ?? '' }}</textarea>
        <div id="error_bathroom_facilities" class="text-danger error"></div>
    </div>

    {{-- URL Gambar (BARU) --}}
    <div class="col-md-12">
        <label for="main_image_path" class="form-label">Path Gambar Utama (Opsional)</label>
        <input type="text" class="form-control" id="main_image_path" name="main_image_path" 
            value="{{ $room->main_image_path ?? '' }}" placeholder="Contoh: /img/rooms/deluxe-101.jpg">
        <div id="error_main_image_path" class="text-danger error"></div>
    </div>

    {{-- Catatan: Field 'room_status_id' dihapus total karena status kini dinamis --}}
</form>
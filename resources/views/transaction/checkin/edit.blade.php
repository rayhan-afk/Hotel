<form id="form-edit-checkin" action="{{ route('transaction.checkin.update', $transaction->id) }}">
    @csrf
    @method('PUT')
    
    {{-- 1. INFO TAMU (Disabled) --}}
    <div class="mb-3">
        <label class="form-label fw-bold">Nama Tamu</label>
        <input type="text" class="form-control bg-light" value="{{ $transaction->customer->name }}" disabled>
    </div>

    {{-- 2. INFO KAMAR (Disabled) --}}
    <div class="mb-3">
        <label for="room_id" class="form-label fw-bold">Kamar</label>
        <input type="text" class="form-control bg-light" 
               value="Room {{ $transaction->room->number }} - {{ $transaction->room->type->name }}" 
               disabled>
        <input type="hidden" name="room_id" value="{{ $transaction->room_id }}">
    </div>

    {{-- 3. PERIODE MENGINAP --}}
    <div class="row">
        {{-- Check In (Disabled) --}}
        <div class="col-md-6 mb-3">
            <label for="check_in" class="form-label fw-bold">Check In</label>
            <input type="date" 
                   class="form-control bg-light" 
                   value="{{ \Carbon\Carbon::parse($transaction->check_in)->format('Y-m-d') }}" 
                   disabled>
            {{-- Hidden input untuk data lama --}}
            <input type="hidden" name="check_in" value="{{ \Carbon\Carbon::parse($transaction->check_in)->format('Y-m-d') }}">
        </div>

        {{-- Check Out (Editable / Extend) --}}
        <div class="col-md-6 mb-3">
            <label for="check_out" class="form-label fw-bold">Check Out (Perpanjang)</label>
            <input type="date" 
                   class="form-control border-primary" 
                   name="check_out" 
                   id="check_out" 
                   value="{{ \Carbon\Carbon::parse($transaction->check_out)->format('Y-m-d') }}" 
                   min="{{ \Carbon\Carbon::parse($transaction->check_in)->addDay()->format('Y-m-d') }}"
                   required>
            <small class="text-muted" style="font-size: 11px">Ubah tanggal untuk extend.</small>
        </div>
    </div>

    {{-- 4. PAKET SARAPAN UTAMA (Tamu Utama) --}}
    <div class="mb-3">
        <label for="breakfast" class="form-label fw-bold">Paket Sarapan (Tamu Utama)</label>
        <select class="form-select border-secondary" name="breakfast" id="breakfast">
            <option value="No" {{ ($transaction->breakfast == 'No' || $transaction->breakfast == 0) ? 'selected' : '' }}>
                Tidak (Tanpa Sarapan)
            </option>
            <option value="Yes" {{ ($transaction->breakfast == 'Yes' || $transaction->breakfast == 1) ? 'selected' : '' }}>
                Ya (Dengan Sarapan)
            </option>
        </select>
    </div>

    {{-- [BARU] 5. LAYANAN TAMBAHAN (EXTRA) --}}
    <div class="card bg-light border-0 mb-4">
        <div class="card-body p-3">
            <h6 class="fw-bold mb-3 text-primary" style="font-size: 14px;">
                <i class="fas fa-plus-circle me-1"></i> Layanan Tambahan (Optional)
            </h6>
            
            <div class="row">
                {{-- Input Extra Bed --}}
                <div class="col-md-6 mb-2">
                    <label for="extra_bed" class="form-label fw-bold small">Extra Bed (+Breakfast)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-bed text-secondary"></i></span>
                        <input type="number" min="0" 
                               class="form-control" 
                               name="extra_bed" 
                               id="extra_bed" 
                               value="{{ $transaction->extra_bed ?? 0 }}">
                    </div>
                    <div class="form-text text-danger fw-bold" style="font-size: 10px;">Rp 200.000 / unit</div>
                </div>

                {{-- Input Extra Breakfast --}}
                <div class="col-md-6 mb-2">
                    <label for="extra_breakfast" class="form-label fw-bold small">Extra Breakfast Only</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-utensils text-secondary"></i></span>
                        <input type="number" min="0" 
                               class="form-control" 
                               name="extra_breakfast" 
                               id="extra_breakfast" 
                               value="{{ $transaction->extra_breakfast ?? 0 }}">
                    </div>
                    <div class="form-text text-danger fw-bold" style="font-size: 10px;">Rp 125.000 / porsi</div>
                </div>
            </div>
            <small class="text-muted d-block mt-2 fst-italic" style="font-size: 11px;">
                *Total harga akan dihitung otomatis saat disimpan.
            </small>
        </div>
    </div>

    {{-- TOMBOL AKSI --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary fw-bold px-4" id="btn-save-edit">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
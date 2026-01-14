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

    {{-- [BARU] 3. JUMLAH TAMU --}}
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="count_person" class="form-label fw-bold">Jml. Dewasa</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-user"></i></span>
                <input type="number" 
                       class="form-control border-primary" 
                       name="count_person" 
                       id="count_person" 
                       value="{{ $transaction->count_person ?? 1 }}" 
                       min="1" required>
            </div>
        </div>
        <div class="col-md-6">
            <label for="count_child" class="form-label fw-bold">Jml. Anak</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-child"></i></span>
                <input type="number" 
                       class="form-control border-primary" 
                       name="count_child" 
                       id="count_child" 
                       value="{{ $transaction->count_child ?? 0 }}" 
                       min="0">
            </div>
        </div>
    </div>

    {{-- 4. PERIODE MENGINAP --}}
    <div class="row">
        {{-- Check In (Disabled) --}}
        <div class="col-md-6 mb-3">
            <label for="check_in" class="form-label fw-bold">Check In</label>
            <input type="date" 
                   class="form-control bg-light" 
                   value="{{ \Carbon\Carbon::parse($transaction->check_in)->format('Y-m-d') }}" 
                   disabled>
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
            <small class="" style="color: #50200C; font-size: 11px">Ubah tanggal untuk extend.</small>
        </div>
    </div>

    {{-- 5. PAKET SARAPAN --}}
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
        <div class="form-text small" style="color: #50200C">
            Pengaturan ini berlaku untuk setiap malam menginap.
        </div>
    </div>

    {{-- TOMBOL AKSI --}}
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary fw-bold px-4" id="btn-save-edit">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>
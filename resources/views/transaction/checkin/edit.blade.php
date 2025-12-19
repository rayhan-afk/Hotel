<form id="form-edit-checkin" action="{{ route('transaction.checkin.update', $transaction->id) }}">
    @csrf
    @method('PUT')
    
    {{-- Nama Tamu (Disabled) --}}
    <div class="mb-3">
        <label class="form-label fw-bold">Nama Tamu</label>
        <input type="text" class="form-control bg-light" value="{{ $transaction->customer->name }}" disabled>
    </div>

    {{-- Pilih Kamar (Disabled / Locked) --}}
    <div class="mb-3">
        <label for="room_id" class="form-label fw-bold">Kamar</label>
        {{-- Tampilkan sebagai input disabled agar user melihat info kamar --}}
        <input type="text" class="form-control bg-light" 
               value="Room {{ $transaction->room->number }} - {{ $transaction->room->type->name }}" 
               disabled>
        
        {{-- Kita tetap butuh mengirim room_id ke server, jadi pakai input hidden --}}
        <input type="hidden" name="room_id" value="{{ $transaction->room_id }}">
    </div>

    <div class="row">
        {{-- Check In (Disabled / Locked) --}}
        <div class="col-md-6 mb-3">
            <label for="check_in" class="form-label fw-bold">Check In</label>
            {{-- 
                PENTING: Format value untuk input type="date" HARUS 'Y-m-d' (Tahun-Bulan-Tanggal).
                Browser akan otomatis menampilkannya sesuai format lokal user (misal mm/dd/yyyy atau dd/mm/yyyy).
            --}}
            <input type="date" 
                   class="form-control bg-light" 
                   value="{{ \Carbon\Carbon::parse($transaction->check_in)->format('Y-m-d') }}" 
                   disabled>
            
            {{-- Kirim check_in hidden agar controller tetap menerima datanya --}}
            <input type="hidden" name="check_in" value="{{ \Carbon\Carbon::parse($transaction->check_in)->format('Y-m-d') }}">
        </div>

        {{-- Check Out (Bisa Diedit / Extend) --}}
        <div class="col-md-6 mb-3">
            <label for="check_out" class="form-label fw-bold">Check Out (Perpanjang)</label>
            <input type="date" 
                   class="form-control border-primary" 
                   name="check_out" 
                   id="check_out" 
                   value="{{ \Carbon\Carbon::parse($transaction->check_out)->format('Y-m-d') }}" 
                   min="{{ \Carbon\Carbon::parse($transaction->check_in)->addDay()->format('Y-m-d') }}"
                   required>
            <small class="text-muted">Ubah tanggal ini untuk memperpanjang durasi.</small>
        </div>
    </div>

    {{-- Opsi Sarapan (BISA DIEDIT) --}}
    <div class="mb-4">
        <label for="breakfast" class="form-label fw-bold">Paket Sarapan</label>
        <select class="form-select border-primary" name="breakfast" id="breakfast">
            <option value="No" {{ ($transaction->breakfast == 'No' || $transaction->breakfast == 0) ? 'selected' : '' }}>
                Tidak (Tanpa Sarapan)
            </option>
            <option value="Yes" {{ ($transaction->breakfast == 'Yes' || $transaction->breakfast == 1) ? 'selected' : '' }}>
                Ya (Dengan Sarapan) + Rp 100.000/malam
            </option>
        </select>
        <small class="text-muted">Biaya total akan otomatis dihitung ulang.</small>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-modal-save" id="btn btn-modal-save">Simpan Perubahan</button>
    </div>
</form>
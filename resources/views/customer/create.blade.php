{{-- FILE: resources/views/customer/create.blade.php --}}
{{-- PENTING: Jangan pakai @extends('template.master') jika file ini dipanggil via Modal/AJAX --}}
{{-- Gunakan div pembungkus biasa --}}

    <div class="card-body p-4">
        {{-- Form Action mengarah ke Customer Store --}}
        <form method="POST" action="{{ route('customer.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                {{-- KOLOM KIRI --}}
                <div class="col-md-6">
                    {{-- NAMA (Wajib) --}}
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold" style="color: #50200C">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" style="color: #50200C;">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- NO HP (Wajib) --}}
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold" style="color: #50200C">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0812xxxx" style="color: #50200C;">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- EMAIL (Opsional) --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold" style="color: #50200C">Alamat Email <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="budi@example.com" style="color: #50200C;">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- PEKERJAAN (Wajib) --}}
                    <div class="mb-3">
                        <label for="job" class="form-label fw-bold" style="color: #50200C">Pekerjaan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('job') is-invalid @enderror" id="job" name="job" value="{{ old('job') }}" placeholder="Wiraswasta, PNS, dll" style="color: #50200C;">
                        @error('job') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- KOLOM KANAN --}}
                <div class="col-md-6">
                    {{-- GRUP TAMU (BARU - Tambahan Khusus) --}}
                    <div class="mb-3">
                        <label for="customer_group" class="form-label fw-bold" style="color: #50200C">Grup Tamu</label>
                        <select class="form-select select2 @error('customer_group') is-invalid @enderror" 
                                name="customer_group" id="customer_group"
                                style="color: #50200C; width: 100%;">
                            {{-- Opsi ini bisa di-loop jika Anda mengirim variabel $groups dari controller --}}
                            <option value="General" {{ old('customer_group') == 'General' ? 'selected' : '' }}>General (Umum)</option>
                            <option value="Corporate" {{ old('customer_group') == 'Corporate' ? 'selected' : '' }}>Corporate (Perusahaan)</option>
                            <option value="Family" {{ old('customer_group') == 'Family' ? 'selected' : '' }}>Family (Keluarga)</option>
                            <option value="Government" {{ old('customer_group') == 'Government' ? 'selected' : '' }}>Government (Pemerintah)</option>
                        </select>
                        <small class="" style="color: #C49A6C;">*Menentukan harga diskon member.</small>
                    </div>

                    {{-- TANGGAL LAHIR (Opsional) --}}
                    <div class="mb-3">
                        <label for="birthdate" class="form-label fw-bold" style="color: #50200C">Tanggal Lahir <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="date" class="form-control @error('birthdate') is-invalid @enderror" id="birthdate" name="birthdate" value="{{ old('birthdate') }}" style="color: #50200C;">
                        @error('birthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- JENIS KELAMIN (Wajib) --}}
                    <div class="mb-3">
                        <label for="gender" class="form-label fw-bold" style="color: #50200C">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" style="color: #50200C;">
                            <option value="" disabled selected>Pilih Gender</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- ALAMAT (Wajib) --}}
                    <div class="mb-3">
                        <label for="address" class="form-label fw-bold" style="color: #50200C">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" placeholder="Jl. Mawar No. 10..." style="color: #50200C;">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- FOTO (Opsional) --}}
                    <div class="mb-3">
                        <label for="avatar" class="form-label fw-bold" style="color:#50200C">Foto Profil <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" style="color: #50200C;">
                        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
<form action="{{ route('customer.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @csrf
    
    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Nama Lengkap</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       style="color: #50200C; border-color: #C49A6C;"
                       name="name" value="{{ old('name', $customer->name) }}" required>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Email User</label>
                {{-- [UPDATED] Input Email sekarang BISA diedit --}}
                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                       style="color: #50200C; border-color: #C49A6C;"
                       name="email" 
                       value="{{ old('email', $customer->user ? $customer->user->email : '') }}" required>
                <small class="text-muted" style="color: #C49A6C !important;">*Pastikan email aktif dan unik.</small>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">No. HP / WhatsApp</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color: #F7F3E4; border-color: #C49A6C; color: #50200C;">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                           style="color: #50200C; border-color: #C49A6C;"
                           name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="Contoh: 0812...">
                </div>
                @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Jenis Kelamin</label>
                <select class="form-select @error('gender') is-invalid @enderror" 
                        style="color: #50200C; border-color: #C49A6C;" name="gender">
                    <option value="Male" {{ old('gender', $customer->gender) == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Female" {{ old('gender', $customer->gender) == 'Female' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('gender') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="col-lg-6">
            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Pekerjaan</label>
                <input type="text" class="form-control @error('job') is-invalid @enderror" 
                       style="color: #50200C; border-color: #C49A6C;"
                       name="job" value="{{ old('job', $customer->job) }}">
                @error('job') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Tanggal Lahir</label>
                <input type="date" class="form-control @error('birthdate') is-invalid @enderror" 
                       style="color: #50200C; border-color: #C49A6C;"
                       name="birthdate" value="{{ old('birthdate', $customer->birthdate) }}">
                @error('birthdate') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Alamat Lengkap</label>
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          style="color: #50200C; border-color: #C49A6C;"
                          name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold" style="color: #50200C;">Ganti Foto (Opsional)</label>
                <div class="d-flex align-items-center gap-3">
                    @php
                        $avatar = $customer->user && $customer->user->avatar 
                            ? asset('img/user/' . $customer->user->name . '-' . $customer->user->id . '/' . $customer->user->avatar)
                            : asset('img/default/default-user.jpg');
                    @endphp
                    <img src="{{ $avatar }}" class="rounded-circle border shadow-sm" 
                         style="width: 50px; height: 50px; object-fit: cover; border-color: #C49A6C !important;">
                    
                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                           style="color: #50200C; border-color: #C49A6C;" name="avatar">
                </div>
                @error('avatar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</form>
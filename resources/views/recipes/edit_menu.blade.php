@extends('template.master')
@section('title', 'Edit Menu: ' . $menu->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('recipes.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <h5 class="mb-0 fw-bold" style="color: #50200C;">Edit Identitas Menu</h5>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    {{-- Tampilkan Error Validation jika ada --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('recipes.updateMenu', $menu->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            {{-- Nama Menu --}}
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Nama Menu</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
                            </div>

                            {{-- Kategori & Harga --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kategori</label>
                                <select class="form-select" name="category" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ $menu->category == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="price" class="form-control" value="{{ old('price', $menu->price) }}" required>
                                </div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $menu->description) }}</textarea>
                            </div>

                            {{-- Gambar --}}
                            <div class="col-12">
                                <label class="form-label fw-bold">Gambar Menu</label>
                                <div class="d-flex align-items-start gap-3">
                                    @if($menu->image)
                                        <div class="border p-1 rounded">
                                            <img src="{{ asset('storage/' . $menu->image) }}" width="100" height="100" style="object-fit: cover;" class="rounded">
                                            <div class="small text-center text-muted mt-1">Saat ini</div>
                                        </div>
                                    @endif
                                    
                                    <div class="flex-grow-1">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <small class="text-muted d-block mt-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Biarkan kosong jika tidak ingin mengubah gambar.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('recipes.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn text-white" style="background-color: #50200C;">
                                <i class="fas fa-save me-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
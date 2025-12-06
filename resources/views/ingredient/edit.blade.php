<form id="form-save-ingredient" action="{{ route('ingredient.update', $ingredient->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label fw-bold">Nama Bahan</label>
        <input type="text" class="form-control" name="name" value="{{ $ingredient->name }}">
        <div id="error_name" class="text-danger error"></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Kategori</label>
            
            {{-- PERUBAHAN: Menggunakan Select dengan logika 'selected' --}}
            <select class="form-select" name="category">
                <option value="" disabled>-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ $ingredient->category == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>

            <div id="error_category" class="text-danger error"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Satuan</label>
            <input type="text" class="form-control" name="unit" value="{{ $ingredient->unit }}">
            <div id="error_unit" class="text-danger error"></div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Stok Saat Ini</label>
        <input type="number" class="form-control" name="stock" value="{{ $ingredient->stock }}" step="0.01">
        <div id="error_stock" class="text-danger error"></div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Keterangan</label>
        <textarea class="form-control" name="description" rows="2">{{ $ingredient->description }}</textarea>
        <div id="error_description" class="text-danger error"></div>
    </div>
</form>
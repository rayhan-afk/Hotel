<form id="form-save-ingredient" action="{{ route('ingredient.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label fw-bold">Nama Bahan</label>
        <input type="text" class="form-control" name="name" placeholder="Contoh: Wortel, Ayam" required>
        <div id="error_name" class="text-danger error"></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Kategori</label>
            <select class="form-select" name="category" required>
                <option value="" selected disabled>-- Pilih Kategori --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
            <div id="error_category" class="text-danger error"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Satuan</label>
            <input type="text" class="form-control" name="unit" placeholder="Kg, Gram, Pcs" required>
            <div id="error_unit" class="text-danger error"></div>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Stok Awal</label>
        <input type="number" class="form-control" name="stock" value="0" step="0.01" min="0" required>
        <div id="error_stock" class="text-danger error"></div>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Keterangan</label>
        <textarea class="form-control" name="description" rows="2" placeholder="Opsional: Catatan tambahan"></textarea>
        <div id="error_description" class="text-danger error"></div>
    </div>
</form>
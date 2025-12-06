<form id="form-save-amenity" action="{{ route('amenity.update', $amenity->id) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="mb-3">
        <label for="nama_barang" class="form-label fw-bold small">Nama Barang</label>
        <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="{{ $amenity->nama_barang }}" required>
        <div class="invalid-feedback" id="error-nama_barang"></div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="satuan" class="form-label fw-bold small">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ $amenity->satuan }}" required>
            <div class="invalid-feedback" id="error-satuan"></div>
        </div>
        <div class="col-md-6 mb-3">
            <label for="stok" class="form-label fw-bold small">Stok</label>
            <input type="number" class="form-control" id="stok" name="stok" value="{{ $amenity->stok }}" min="0" required>
            <div class="invalid-feedback" id="error-stok"></div>
        </div>
    </div>

    <div class="mb-3">
        <label for="keterangan" class="form-label fw-bold small">Keterangan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ $amenity->keterangan }}</textarea>
        <div class="invalid-feedback" id="error-keterangan"></div>
    </div>
</form>
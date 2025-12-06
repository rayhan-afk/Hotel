<form id="form-save-amenity" class="row g-3" method="POST" action="{{ route('amenity.store') }}">
    @csrf
    <div class="col-md-12">
        <label for="nama_barang" class="form-label">Nama Barang</label>
        <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="ex: Sabun Cair">
        <div id="error_nama_barang" class="text-danger error"></div>
    </div>
    <div class="col-md-6">
        <label for="satuan" class="form-label">Satuan</label>
        <input type="text" class="form-control" id="satuan" name="satuan" placeholder="ex: Pcs, Botol">
        <div id="error_satuan" class="text-danger error"></div>
    </div>
    <div class="col-md-6">
        <label for="stok" class="form-label">Stok Awal</label>
        <input type="number" class="form-control" id="stok" name="stok" value="0">
        <div id="error_stok" class="text-danger error"></div>
    </div>
    <div class="col-md-12">
        <label for="keterangan" class="form-label">Keterangan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Opsional"></textarea>
        <div id="error_keterangan" class="text-danger error"></div>
    </div>
</form>
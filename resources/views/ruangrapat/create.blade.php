<form id="form-save-ruangrapat" class="row g-3" method="POST" action="{{ route('ruangrapat.store') }}">
    @csrf
    <div class="col-md-12">
        <label for="name" class="form-label">Nama Paket</label>
        <input type="text" class="form-control" id="name" name="name"
            placeholder="Contoh: Paket Half Day">
        <div id="error_name" class="text-danger error"></div>
    </div>

    <div class="col-md-12">
        <label for="harga" class="form-label">Harga</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control" id="harga" name="harga"
                placeholder="0">
        </div>
        <div id="error_harga" class="text-danger error"></div>
    </div>

    <div class="col-md-12">
        <label for="isi_paket" class="form-label">Isi Paket</label>
        <textarea class="form-control" id="isi_paket" name="isi_paket" rows="3"
            placeholder="Deskripsikan apa saja yang termasuk dalam paket ini"></textarea>
        <div id="error_isi_paket" class="text-danger error"></div>
    </div>

    <div class="col-md-12">
        <label for="fasilitas" class="form-label">Fasilitas</label>
        <textarea class="form-control" id="fasilitas" name="fasilitas" rows="3"
            placeholder="Sebutkan fasilitas yang didapat (Projector, Sound system, dll)"></textarea>
        <div id="error_fasilitas" class="text-danger error"></div>
    </div>
</form>
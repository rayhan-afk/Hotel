@extends('template.master')
@section('title', 'Guest Folio')

@section('content')
<div class="container-fluid mt-4">

    {{-- HEADER INFORMASI --}}
    <div class="card mb-4 shadow-sm border-0" style="background-color: #F7F3E4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1 fw-bold" style="color: #50200C;">Tagihan Tamu</h3>
                    <p class="text-muted mb-0">
                        <i class="fas fa-door-open me-1"></i> Kamar: <strong>{{ $transaction->room->number }}</strong> 
                        <span class="mx-2">|</span> 
                        <i class="fas fa-user me-1"></i> Tamu: <strong>{{ $transaction->customer->name }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('transaction.invoice.print', $transaction->id) }}" target="_blank" class="btn text-white me-2 px-3 shadow-sm" style="background-color: #50200C;">
                        <i class="fas fa-print me-1"></i> Cetak Invoice
                    </a>
                    <a href="{{ route('fo.cashier.index') }}" class="btn btn-outline-secondary px-3 shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        {{-- KOLOM KIRI --}}
        <div class="col-md-4">
            
            {{-- 1. QUICK ACTION --}}
            <div class="card shadow-sm border-0 mb-4" style="background: #FFFF; color: #50200C;">
                <div class="card-header bg-transparent fw-bold border-0 pt-3 ps-3">
                    <i class="fas fa-bolt me-2" style="color: #FAE8A4"></i>Quick Actions
                </div>
                <div class="card-body pt-0 pb-3 px-3">
                    
                    {{-- EXTRA BED --}}
                    <div class="d-flex gap-2 mb-2">
                        <form action="{{ route('fo.cashier.store_charge', $transaction->id) }}" method="POST" class="w-100">
                            @csrf
                            <input type="hidden" name="type" value="Miscellaneous">
                            <input type="hidden" name="item_name" value="Extra Bed (+Breakfast)">
                            <input type="hidden" name="amount" value="200000">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-dark w-100 text-start shadow-sm position-relative overflow-hidden" title="Langsung tambah (Rp 200.000)">
                                <div class="d-flex justify-content-between align-items-center position-relative" style="z-index: 1;">
                                    <span><i class="fas fa-bed me-2"></i>Extra Bed (+ Breakfast)</span>
                                    <span class="badge bg-white text-dark border fw-bold">+ Add</span>
                                </div>
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-dark shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCustomBed" title="Custom Harga">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>

                    {{-- EXTRA BREAKFAST --}}
                    <div class="d-flex gap-2">
                        <form action="{{ route('fo.cashier.store_charge', $transaction->id) }}" method="POST" class="w-100">
                            @csrf
                            <input type="hidden" name="type" value="Room Service">
                            <input type="hidden" name="item_name" value="Extra Breakfast Only">
                            <input type="hidden" name="amount" value="125000">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-warning w-100 text-start shadow-sm" title="Langsung tambah (Rp 125.000)">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-utensils me-2"></i>Breakfast Only</span>
                                    <span class="badge bg-white text-dark border fw-bold">+ Add</span>
                                </div>
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-warning text-dark border-warning shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalCustomBreakfast" title="Custom Harga">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>

                </div>
            </div>

            {{-- 2. FORM INPUT MANUAL --}}
            <div class="card shadow border-0 mb-4" style="background: white; border-radius: 12px; overflow: hidden;">
                <div class="card-header fw-bold d-flex align-items-center py-3" style="background: #C49A6C; color: #50200C;">
                    <i class="fas fa-keyboard me-2"></i> Tambah Tagihan Lainnya
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('fo.cashier.store_charge', $transaction->id) }}" method="POST">
                        @csrf
                        <div class="form-floating mb-3" style="color: #50200C">
                            <select name="type" class="form-select border-0 bg-light shadow-sm" id="floatingSelect" required>
                                <option value="" disabled selected>Pilih Kategori...</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Room Service">Room Service</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Lost and Breakage">Denda / Kerusakan</option>
                                <option value="Miscellaneous">Lain-lain</option>
                                <option value="Deposit">Deposit</option> 
                            </select>
                            <label for="floatingSelect" class="" style="color: #50200C">Kategori Sales</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="text" name="item_name" class="form-control border-0 bg-light shadow-sm" id="floatingItem" placeholder="Item" required>
                            <label for="floatingItem" class="" style="color: #50200C">Nama Item / Keterangan</label>
                        </div>
                        <div class="row g-2">
                            <div class="col-7 mb-3">
                                <div class="form-floating">
                                    <input type="number" name="amount" class="form-control border-0 bg-light shadow-sm" id="floatingAmount" placeholder="0" required>
                                    <label for="floatingAmount" class="" style="color: #50200C">Harga (Rp)</label>
                                </div>
                            </div>
                            <div class="col-5 mb-3">
                                <div class="form-floating">
                                    <input type="number" name="qty" value="1" min="1" class="form-control border-0 bg-light shadow-sm" id="floatingQty" required>
                                    <label for="floatingQty" class="" style="color: #50200C">Qty</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating mb-4">
                            <textarea name="note" class="form-control border-0 bg-light shadow-sm" id="floatingNote" style="height: 80px" placeholder="Catatan"></textarea>
                            <label for="floatingNote" class="" style="color: #50200C">Catatan (Opsional)</label>
                        </div>
                        <button type="submit" class="btn w-100 fw-bold py-2 shadow text-white" 
                                style="background: #50200C; border: none; border-radius: 8px;">
                            <i class="fas fa-paper-plane me-2"></i> Simpan ke Tagihan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL RINCIAN --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-bold py-3" style="background-color: #C49A6C; border-bottom: 2px solid #f0f0f0; color: #50200C;">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Rincian Tagihan
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-uppercase">
                                    <th class="ps-4 py-3">Item & Keterangan</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Total (Rp)</th>
                                    <th class="text-center" width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- BIAYA KAMAR --}}
                                @php
                                    $totalCharges = $transaction->charges->sum('total');
                                    $pureRoomBill = $transaction->total_price - $totalCharges;
                                @endphp
                                <tr style="background-color: #FFFF; color: #50200C;">
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold" style="color: #50200C">Room Charge & Extras</div>
                                        <small class="">
                                            <i class="fas fa-bed me-1" style="color: #50200C"></i> {{ $transaction->room->type->name }} | 
                                            {{ $transaction->getDateDifferenceWithPlural() }}
                                        </small>
                                    </td>
                                    <td><span class="badge badge-brown px-3 py-2 rounded-pill">ROOM</span></td>
                                    <td class="text-end fw-bold" style="color: #50200C">{{ number_format($pureRoomBill, 0, ',', '.') }}</td>
                                    <td class="text-center" style="color: #50200C"><i class="fas fa-lock opacity-50"></i></td> 
                                </tr>

                                {{-- LOOPING CHARGES --}}
                                @forelse($transaction->charges as $charge)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold" style="color: #50200C">{{ $charge->item_name }} <span class="fw-normal ms-1" style="color: #50200C">x {{ $charge->qty }}</span></div>
                                        @if($charge->note) <small class="d-block fst-italic" style="color: #50200C"><i class="fas fa-sticky-note me-1" style="color: #50200C"></i> {{ $charge->note }}</small> @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeColor = 'bg-info';
                                            if($charge->type == 'Laundry') $badgeColor = 'badge-reserved';
                                            if($charge->type == 'Room Service') $badgeColor = 'badge-pending';
                                            if($charge->type == 'Lost and Breakage') $badgeColor = 'badge-rejected';
                                            if($charge->type == 'Miscellaneous') $badgeColor = 'badge-orange';
                                        @endphp
                                        <span class="badge {{ $badgeColor }} px-2 py-1">{{ $charge->type }}</span>
                                    </td>
                                    <td class="text-end fw-bold" style="color: #50200C">{{ number_format($charge->total, 0, ',', '.') }}</td>
                                    
                                    {{-- TOMBOL HAPUS (FIXED: PASTI BISA DIKLIK) --}}
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-light text-danger border-0 hover-shadow"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteConfirmation"
                                                onclick="setDeleteAction('{{ route('fo.cashier.destroy_charge', $charge->id) }}')"
                                                title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-5 fst-italic" style="color: #50200C">Belum ada tagihan tambahan.</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light" style="border-top: 2px solid #50200C;">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold pt-3 pb-3" style="color: #50200C">Grand Total</td>
                                    <td class="text-end fw-bold fs-4 pt-3 pb-3" style="color: #50200C;">
                                        Rp {{ number_format($transaction->total_price, 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALS CUSTOM ADD --}}
<div class="modal fade" id="modalCustomBed" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background: #212529;">
                <h5 class="modal-title fs-6">Custom Extra Bed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('fo.cashier.store_charge', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <input type="hidden" name="type" value="Miscellaneous">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Item</label>
                        <input type="text" name="item_name" class="form-control" value="Extra Bed (+Breakfast)">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Harga</label>
                        <input type="number" name="amount" class="form-control border-dark" value="200000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Qty</label>
                        <input type="number" name="qty" class="form-control" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer p-2 bg-white">
                    <button type="submit" class="btn btn-dark w-100 btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCustomBreakfast" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-dark" style="background: #ffca2c;">
                <h5 class="modal-title fs-6">Custom Breakfast</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('fo.cashier.store_charge', $transaction->id) }}" method="POST">
                @csrf
                <div class="modal-body bg-light">
                    <input type="hidden" name="type" value="Room Service">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Item</label>
                        <input type="text" name="item_name" class="form-control" value="Extra Breakfast Only">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Harga</label>
                        <input type="number" name="amount" class="form-control border-warning" value="125000">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Qty</label>
                        <input type="number" name="qty" class="form-control" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer p-2 bg-white">
                    <button type="submit" class="btn btn-warning w-100 btn-sm fw-bold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI DELETE (PASTI JALAN) --}}
<div class="modal fade" id="modalDeleteConfirmation" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-delete-fit">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i> Hapus Item?
                </h5>
                <button type="button" class="btn-close btn-close-brown" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4" style="background-color: #F7F3E4">
                <div class="mb-3" style="color: #A94442">
                    <i class="fas fa-trash-alt fa-3x opacity-50"></i>
                </div>
                <p class="mb-1 fw-bold" style="color: #50200C">Anda yakin ingin menghapus?</p>
                <small class="" style="color: #50200C">Total transaksi akan berkurang.</small>
            </div>
            <div class="modal-footer border-0 justify-content-center p-2" style="background-color: #F7F3E4">
                <button type="button" class="btn btn-modal-close btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                
                {{-- Form Delete --}}
                <form id="formDeleteCharge" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-modal-save btn-sm px-4 fw-bold">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer')
{{-- Script Super Sederhana & Kuat (Tanpa Event Listener Rumit) --}}
<script>
    function setDeleteAction(url) {
        document.getElementById('formDeleteCharge').action = url;
    }
</script>
@endsection
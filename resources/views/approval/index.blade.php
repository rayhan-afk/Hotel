@extends('template.master')
@section('title', 'Approval Management')

@section('content')
<style>
    /* Badge Status */
    .status-badge {
        font-size: 0.8rem; 
        padding: 0.4em 0.8em; 
        border-radius: 50px; 
        font-weight: 700; 
        display: inline-flex; 
        align-items: center; 
        gap: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-pending { background-color: #fff8e1; color: #b7791f; border: 1px solid #f6e05e; }
    .badge-approved { background-color: #def7ec; color: #046c4e; border: 1px solid #84e1bc; }
    .badge-rejected { background-color: #fde8e8; color: #c81e1e; border: 1px solid #f8b4b4; }

    /* Fix Dropdown Terpotong & Z-Index Issue */
    .professional-table-container { 
        overflow: visible !important; 
    }
    
    .table-header {
        position: relative;
        z-index: 10; 
    }

    .table-responsive {
        overflow-x: auto;
        min-height: 300px;
        position: relative;
        z-index: 1;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 style="color: #50200C"><i class="fas fa-clipboard-check me-2" style="color: #50200C"></i>Approval Management</h3>
                    <p class="" style="color: #50200C">Kelola persetujuan perubahan Tipe Kamar, Paket Rapat, dan Data Kamar</p>
                </div>
                <div>
                    <span class="badge fs-6 shadow-sm" style="background-color: #FAE8A4; color: #50200C">
                        <i class="fas fa-clock me-1"></i>{{ $pendingCount }} Pending
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="professional-table-container">
        <div class="table-header d-flex justify-content-between align-items-center" style="position: relative; z-index: 999;">
            <h4><i class="fas fa-list me-2"></i>Daftar Approval</h4>
            <div>
                <label class="me-2 fw-bold" style="color: #50200C">Filter Status:</label>
                <select id="status_filter" class="form-select d-inline-block shadow-sm border-primary" 
                        style="color: #50200C; width: 220px; cursor: pointer; font-weight: 500; position: relative; z-index: 1000;">
                    <option value="all">📂 Semua Status</option>
                    <option value="pending" selected>🕒 Pending (Menunggu)</option>
                    <option value="approved">✅ Approved (Disetujui)</option>
                    <option value="rejected">❌ Rejected (Ditolak)</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="approval-table" class="professional-table table table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%">No</th>
                        <th>Nama Item</th>
                        <th>Diajukan Oleh</th>
                        <th class="text-center">Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        
        <div class="table-footer"></div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="detail-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title" ><i class="fas fa-eye me-2"></i>Detail Perubahan</h5>
                <button type="button" class="btn-close" style="color: #50200C" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body" style="background-color: #F7F3E4"></div>
        </div>
    </div>
</div>

{{-- MODAL APPROVE (CUSTOM STYLE) --}}
<div class="modal fade" id="approve-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #F7F3E4; color: #50200C">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Setuju</h5>
                <button type="button" class="btn-close" style="color: #50200C" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-approve">
                <div class="modal-body">
                    <p>Yakin ingin <strong>menyetujui</strong> perubahan ini?</p>
                    <textarea style="color: #50200C" class="form-control" name="notes" rows="3" placeholder="Catatan (opsional)"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modal-save"><i class="fas fa-check"></i> Setuju</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL REJECT (CUSTOM STYLE) --}}
<div class="modal fade" id="reject-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #F7F3E4; color: #50200C">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Tolak</h5>
                <button type="button" class="btn-close" style="color: #50200C" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-reject">
                <div class="modal-body">
                    <p>Yakin ingin <strong>menolak</strong> perubahan ini?</p>
                    <textarea style="color: #50200C" class="form-control" name="notes" rows="3" placeholder="Alasan penolakan" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modal-save"><i class="fas fa-times"></i> Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
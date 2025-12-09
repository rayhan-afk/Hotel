@extends('template.master')
@section('title', 'Approval Management')

@section('content')
<style>
    /* [STYLE 1] Badge Status Kapsul Modern */
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
    .badge-pending { 
        background-color: #fff8e1; color: #b7791f; border: 1px solid #f6e05e; 
    }
    .badge-approved { 
        background-color: #def7ec; color: #046c4e; border: 1px solid #84e1bc; 
    }
    .badge-rejected { 
        background-color: #fde8e8; color: #c81e1e; border: 1px solid #f8b4b4; 
    }

    /* [STYLE 2] FIX DROPDOWN FILTER (PENTING!) */
    .professional-table-container {
        overflow: visible !important; 
    }
    
    .filter-wrapper {
        position: relative;
        z-index: 9999;
    }
    /* 🔥 TAMBAHKAN DARI SINI 👇 */
    .modal-backdrop {
        z-index: 10000 !important;
    }

    .modal {
        z-index: 10050 !important;
    }

    .modal-dialog {
        z-index: 10060 !important;
    }

    .sidebar, .main-sidebar, aside {
        z-index: 1030 !important;
    }

    header, .main-header, nav {
        z-index: 1040 !important;
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

    <!-- Gunakan style overflow: visible agar dropdown tidak terpotong -->
    <div class="professional-table-container" style="overflow: visible !important;">
        <div class="table-header d-flex justify-content-between align-items-center filter-wrapper">
            <h4><i class="fas fa-list me-2"></i>Daftar Approval</h4>
            <div>
                <label class="me-2 fw-bold" style="color: #50200C">Filter Status:</label>
                <!-- Tambahkan z-index tinggi pada select -->
                <select id="status_filter" class="form-select d-inline-block shadow-sm border-primary" 
                        style="color: #50200C; width: 220px; cursor: pointer; font-weight: 500; position: relative; z-index: 10000;">
                    <!-- [FIX] Pastikan value sesuai database (huruf kecil) -->
                    <option value="all">📂 Semua Status</option>
                    <option value="pending" selected>🕒 Pending (Menunggu)</option>
                    <option value="approved">✅ Approved (Disetujui)</option>
                    <option value="rejected">❌ Rejected (Ditolak)</option>
                </select>
            </div>
        </div>

        <div class="table-responsive" style="position: relative; z-index: 1;">
            <table id="approval-table" class="table table-hover align-middle" style="width: 100%;">
                <thead class="bg-light">
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
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="detail-modal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4; color: #50200C;">
                <h5 class="modal-title" ><i class="fas fa-eye me-2"></i>Detail Perubahan</h5>
                <button type="button" class="btn-close btn-close-brown" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-body" style="background-color: #F7F3E4"></div>
        </div>
    </div>
</div>

{{-- MODAL APPROVE --}}
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

{{-- MODAL REJECT --}}
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Helper: Load JS
    function loadScript(src, callback) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = callback;
        script.onerror = function() { console.error('Gagal memuat script:', src); };
        document.head.appendChild(script);
    }

    // Helper: Load CSS
    function loadCSS(href) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }

    // --- URUTAN LOADING ---
    if (typeof window.jQuery === 'undefined') {
        loadScript('https://code.jquery.com/jquery-3.6.0.min.js', function() {
            checkDataTables(window.jQuery);
        });
    } else {
        checkDataTables(window.jQuery);
    }

    function checkDataTables($) {
        if (!$.fn.DataTable) {
            loadCSS('https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css');
            loadScript('https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', function() {
                loadScript('https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js', function() {
                    checkSweetAlert($);
                });
            });
        } else {
            checkSweetAlert($);
        }
    }

    function checkSweetAlert($) {
        if (typeof Swal === 'undefined') {
            loadScript('https://cdn.jsdelivr.net/npm/sweetalert2@11', function() {
                initializeApprovalPage($);
            });
        } else {
            initializeApprovalPage($);
        }
    }

    // --- FUNGSI UTAMA HALAMAN ---
    function initializeApprovalPage($) {
        'use strict';
        console.log('🚀 Approval Script Started - v6.0 (Realtime Filter Fixed)');

        let selectedApprovalId = null;
        let approvalTable = null; // [FIX] Variabel didefinisikan di scope ini agar konsisten

        // --- SETUP DATA TABLE ---
        function initDataTable() {
            if ($('#approval-table').length === 0) return;

            // Hancurkan datatable lama jika ada
            if ($.fn.DataTable.isDataTable('#approval-table')) {
                $('#approval-table').DataTable().destroy();
            }

            // [FIX] Assign instance ke variabel approvalTable
            approvalTable = $('#approval-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/approval/data', 
                    type: 'GET',
                    data: function(d) {
                        // [FIX] Ambil value langsung dari elemen saat request ajax dikirim
                        d.status = $('#status_filter').val(); 
                        console.log("📡 Request Data dengan Status:", d.status);
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTable Error:', xhr);
                    }
                },
                columns: [
                    // Kolom No Urut Terbalik
                    { 
                        data: null, 
                        width: '5%', 
                        className: 'text-center align-middle fw-bold',
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return meta.settings._iRecordsDisplay - (meta.settings._iDisplayStart + meta.row);
                        }
                    },
                    { data: 'item_name', className: 'fw-bold align-middle' },
                    { data: 'requester_name', className: 'align-middle' },
                    { 
                        data: 'status',
                        className: 'align-middle text-center',
                        render: function(data) {
                            const status = (data || '').toLowerCase();
                            
                            if (status === 'pending') {
                                return '<span class="status-badge badge-pending"><i class="fas fa-clock fa-spin"></i> Pending</span>';
                            } 
                            else if (status === 'approved') {
                                return '<span class="status-badge badge-approved"><i class="fas fa-check-circle"></i> Approved</span>';
                            } 
                            else if (status === 'rejected') {
                                return '<span class="status-badge badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>';
                            } 
                            else {
                                return '<span class="badge bg-secondary">Unknown</span>';
                            }
                        }
                    },
                    { data: 'created_at', className: 'align-middle' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            const status = (row.status || '').toLowerCase();
                            let btns = `<button class="btn btn-sm btn-info text-white me-1 btn-detail shadow-sm rounded-circle" data-id="${row.id}" title="Detail" style="width: 32px; height: 32px; padding: 0; line-height: 32px;"><i class="fas fa-eye"></i></button>`;
                            
                            if (status === 'pending') {
                                btns += `<button class="btn btn-sm btn-success me-1 btn-approve shadow-sm rounded-circle" data-id="${row.id}" title="Approve" style="width: 32px; height: 32px; padding: 0; line-height: 32px;"><i class="fas fa-check"></i></button>`;
                                btns += `<button class="btn btn-sm btn-danger btn-reject shadow-sm rounded-circle" data-id="${row.id}" title="Reject" style="width: 32px; height: 32px; padding: 0; line-height: 32px;"><i class="fas fa-times"></i></button>`;
                            }
                            return btns;
                        }
                    }
                ],
                order: [[4, 'desc']], 
                pageLength: 10,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat Data...',
                    emptyTable: 'Tidak ada data approval',
                    zeroRecords: 'Tidak ditemukan data yang cocok'
                }
            });
        }

        // --- SETUP EVENT HANDLERS ---
        function setupEventHandlers() {
            // [FIX UTAMA] Event Listener Filter
            // Pastikan event handler dimatikan dulu sebelum di-bind untuk mencegah double trigger
            $(document).off('change', '#status_filter');
            $(document).on('change', '#status_filter', function() {
                var selectedVal = $(this).val();
                console.log("🔄 Dropdown Berubah: " + selectedVal);
                
                // Reload via variable instance
                if(approvalTable) {
                    approvalTable.ajax.reload(); 
                } else {
                    // Fallback jika variable null (ambil ulang dari DOM)
                    $('#approval-table').DataTable().ajax.reload();
                }
            });

            // Detail Button
            $(document).off('click', '.btn-detail').on('click', '.btn-detail', function() {
                const id = $(this).data('id');
                $('#detail-body').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
                try { new bootstrap.Modal(document.getElementById('detail-modal')).show(); } catch(e) { $('#detail-modal').modal('show'); }
                
                $.get('/approval/' + id)
                    .done(function(response) { $('#detail-body').html(response.view); })
                    .fail(function() { $('#detail-body').html('<div class="alert alert-danger">Gagal memuat detail</div>'); });
            });

            // Approve Button
            $(document).off('click', '.btn-approve').on('click', '.btn-approve', function() {
                selectedApprovalId = $(this).data('id');
                $('#form-approve')[0].reset();
                try { new bootstrap.Modal(document.getElementById('approve-modal')).show(); } catch(e) { $('#approve-modal').modal('show'); }
            });

            // Reject Button
            $(document).off('click', '.btn-reject').on('click', '.btn-reject', function() {
                selectedApprovalId = $(this).data('id');
                $('#form-reject')[0].reset();
                try { new bootstrap.Modal(document.getElementById('reject-modal')).show(); } catch(e) { $('#reject-modal').modal('show'); }
            });

            // Submit Approve
            $('#form-approve').off('submit').on('submit', function(e) {
                e.preventDefault();
                processForm('/approval/' + selectedApprovalId + '/approve', $(this));
            });

            // Submit Reject
            $('#form-reject').off('submit').on('submit', function(e) {
                e.preventDefault();
                processForm('/approval/' + selectedApprovalId + '/reject', $(this));
            });
        }

        function processForm(url, form) {
            const btn = form.find('button[type="submit"]');
            const oriHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Proses...');
            
            $.ajax({
                url: url, 
                method: 'POST', 
                data: form.serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    $('.modal').each(function() { try{ bootstrap.Modal.getInstance(this).hide(); }catch(e){ $(this).modal('hide'); } });
                    if(typeof Swal !== 'undefined') Swal.fire('Sukses', res.message, 'success'); else alert(res.message);
                    
                    // [FIX] Reload tabel otomatis setelah approve/reject sukses
                    if(approvalTable) approvalTable.ajax.reload();
                },
                error: function(xhr) { alert('Error: ' + (xhr.responseJSON?.message || 'Gagal')); },
                complete: function() { btn.prop('disabled', false).html(oriHtml); }
            });
        }

        initDataTable();
        setupEventHandlers();
    }
});
</script>
@endsection
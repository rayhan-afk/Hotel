$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("approval")) return;

    console.log("🚀 Approval Script Loaded (Round Buttons)");

    let selectedApprovalId = null;
    let datatable = null;

    // --- 1. INISIALISASI DATATABLE ---
    function initTable() {
        const tableElement = $("#approval-table");

        if (tableElement.length > 0) {
            datatable = tableElement.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/approval/data",
                    type: "GET",
                    data: function (d) {
                        const statusVal = $("#status_filter").val();
                        if (statusVal && statusVal !== "all") {
                            d.status = statusVal;
                        }
                    },
                    error: function (xhr) {
                        console.error("DataTable Error:", xhr);
                    },
                },
                columns: [
                    {
                        name: "id",
                        data: "id",
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false,
                        searchable: false,
                        width: "5%"
                    },
                    {
                        name: "item_name",
                        data: "item_name",
                        className: "fw-bold",
                        orderable: false 
                    },
                    {
                        name: "requester_name",
                        data: "requester_name",
                        orderable: false 
                    },
                    {
                        name: "status",
                        data: "status",
                        className: "text-center",
                        render: function (data) {
                            if (data === "pending") {
                                return '<span class="status-badge badge-pending"><i class="fas fa-clock fa-spin"></i> Pending</span>';
                            } else if (data === "approved") {
                                return '<span class="status-badge badge-approved"><i class="fas fa-check-circle"></i> Approved</span>';
                            } else if (data === "rejected") {
                                return '<span class="status-badge badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>';
                            } else {
                                return '<span class="badge bg-secondary">Unknown</span>';
                            }
                        },
                    },
                    {
                        name: "created_at",
                        data: "created_at"
                    },
                    {
                        // Kolom Aksi
                        name: "id",
                        data: "id",
                        orderable: false,
                        searchable: false,
                        className: "text-nowrap text-center",
                        render: function (id, type, row) {
                            let buttons = `<div class="d-flex justify-content-center gap-1">`;

                            // [UPDATE] Tombol Detail (Biru Bulat)
                            buttons += `
                                <button class="btn btn-info btn-sm text-white rounded-circle shadow-sm btn-detail"
                                    style="width: 32px; height: 32px; padding: 0; line-height: 32px;"
                                    data-id="${id}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                            `;

                            // Tombol Approve & Reject (Hanya jika Pending)
                            if (row.status === "pending") {
                                // [UPDATE] Approve (Hijau Bulat)
                                buttons += `
                                    <button class="btn btn-success btn-sm text-white rounded-circle shadow-sm btn-approve"
                                        style="width: 32px; height: 32px; padding: 0; line-height: 32px;"
                                        data-id="${id}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Setujui">
                                        <i class="fas fa-check"></i>
                                    </button>
                                `;
                                
                                // [UPDATE] Reject (Merah Bulat)
                                buttons += `
                                    <button class="btn btn-danger btn-sm text-white rounded-circle shadow-sm btn-reject"
                                        style="width: 32px; height: 32px; padding: 0; line-height: 32px;"
                                        data-id="${id}"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                `;
                            }

                            buttons += `</div>`;
                            return buttons;
                        },
                    },
                ],
                order: [[4, 'desc']], 
                drawCallback: function() {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            });
        }
    }

    // --- 2. EVENT HANDLERS ---

    // Filter Status Change
    $(document).off("change", "#status_filter").on("change", "#status_filter", function () {
        if(datatable) datatable.ajax.reload(); 
    });

    // Detail Button
    $(document).off("click", '.btn-detail').on("click", '.btn-detail', async function () {
        const id = $(this).data("id");
        const detailModal = new bootstrap.Modal(document.getElementById("detail-modal"));

        $("#detail-body").html(
            '<div class="d-flex justify-content-center py-5 align-items-center"><div class="spinner-border text-primary me-2" role="status"></div><span>Memuat data...</span></div>'
        );
        detailModal.show();

        try {
            const response = await $.get(`/approval/${id}`);
            if(response.view) {
                $("#detail-body").html(response.view);
            } else {
                $("#detail-body").html('<div class="alert alert-danger">Data tidak valid.</div>');
            }
        } catch (e) {
            $("#detail-body").html('<div class="alert alert-danger text-center">Gagal memuat detail.</div>');
        }
    });

    // Approve Button
    $(document).off("click", '.btn-approve').on("click", '.btn-approve', function () {
        selectedApprovalId = $(this).data("id");
        $("#form-approve")[0].reset(); 
        const approveModal = new bootstrap.Modal(document.getElementById("approve-modal"));
        approveModal.show();
    });

    // Reject Button
    $(document).off("click", '.btn-reject').on("click", '.btn-reject', function () {
        selectedApprovalId = $(this).data("id");
        $("#form-reject")[0].reset(); 
        const rejectModal = new bootstrap.Modal(document.getElementById("reject-modal"));
        rejectModal.show();
    });

    // --- 3. PROCESS SUBMIT ---

    function processApproval(url, form, modalId) {
        const btn = form.find('button[type="submit"]');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Proses...');
        
        $.ajax({
            url: url, 
            method: 'POST', 
            data: form.serialize(),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(res) {
                const modalEl = document.getElementById(modalId);
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: res.message || "Berhasil!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    alert(res.message);
                }
                
                // Reload Tabel
                if(datatable) datatable.ajax.reload(null, false);
            },
            error: function(xhr) { 
                if(typeof Swal !== 'undefined') {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                } else {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal')); 
                }
            },
            complete: function() { 
                btn.prop('disabled', false).html(originalText); 
            }
        });
    }

    $(document).on("submit", "#form-approve", function(e) {
        e.preventDefault();
        processApproval(`/approval/${selectedApprovalId}/approve`, $(this), 'approve-modal');
    });

    $(document).on("submit", "#form-reject", function(e) {
        e.preventDefault();
        processApproval(`/approval/${selectedApprovalId}/reject`, $(this), 'reject-modal');
    });

    initTable();
});
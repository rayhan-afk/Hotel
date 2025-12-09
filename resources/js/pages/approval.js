$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("approval")) return;

    let selectedApprovalId = null;

    const datatable = $("#approval-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/approval",
            type: "GET",
            // dataSrc: "aaData",
            data: function (d) {
                const statusFilter = $("#status_filter").val();
                if (statusFilter && statusFilter !== "all") {
                    d.status = statusFilter;
                }
            },
            error: function (xhr) {
                console.error("Error:", xhr);
            },
        },
        columns: [
            {
                data: "id",
                className: "text-center align-middle",
                width: "5%",
            },
            {
                data: "type",
                className: "align-middle",
            },
            {
                data: "item_name",
                className: "fw-bold align-middle",
            },
            {
                data: "requester_name",
                className: "align-middle",
            },
            {
                data: "status",
                className: "align-middle",
                render: function (data) {
                    if (data === "pending") {
                        return '<span class="badge badge-pending"><i class="fas fa-clock me-1"></i>Pending</span>';
                    } else if (data === "approved") {
                        return '<span class="badge badge-approved"><i class="fas fa-check me-1"></i>Approved</span>';
                    } else {
                        return '<span class="badge badge-rejected"><i class="fas fa-times me-1"></i>Rejected</span>';
                    }
                },
            },
            {
                data: "created_at",
                className: "align-middle",
            },
            {
                data: "id",
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
                render: function (id, type, row) {
                    let buttons = `
                        <button class="btn btn-sm btn-light border text-info shadow-sm me-1" 
                            data-action="detail" data-id="${id}" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;

                    // Hanya tampilkan Approve/Reject untuk status pending
                    if (row.status === "pending") {
                        buttons += `
                            <button class="btn btn-sm btn-light border text-success shadow-sm me-1" 
                                data-action="approve" data-id="${id}" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-light border text-danger shadow-sm" 
                                data-action="reject" data-id="${id}" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }

                    return buttons;
                },
            },
        ],
    });

    // Filter Status
    $("#status_filter").on("change", function () {
        datatable.ajax.reload();
    });

    // Detail Button
    $(document).on("click", '[data-action="detail"]', async function () {
        const id = $(this).data("id");
        const detailModal = new bootstrap.Modal(
            document.getElementById("detail-modal")
        );

        $("#detail-body").html(
            '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>'
        );
        detailModal.show();

        try {
            const response = await $.get(`/approval/${id}`);
            $("#detail-body").html(response.view);
        } catch (e) {
            $("#detail-body").html(
                '<div class="text-danger">Gagal memuat detail.</div>'
            );
        }
    });

    // Approve Button
    $(document).on("click", '[data-action="approve"]', function () {
        selectedApprovalId = $(this).data("id");
        const approveModal = new bootstrap.Modal(
            document.getElementById("approve-modal")
        );
        approveModal.show();
    });

    // Reject Button
    $(document).on("click", '[data-action="reject"]', function () {
        selectedApprovalId = $(this).data("id");
        const rejectModal = new bootstrap.Modal(
            document.getElementById("reject-modal")
        );
        rejectModal.show();
    });

    // Submit Approve
    $("#form-approve").on("submit", async function (e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn
            .attr("disabled", true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');

        try {
            const response = await $.ajax({
                url: `/approval/${selectedApprovalId}/approve`,
                method: "POST",
                data: $(this).serialize(),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            });

            bootstrap.Modal.getInstance(
                document.getElementById("approve-modal")
            ).hide();

            Swal.fire({
                icon: "success",
                title: "Approved!",
                text: response.message,
                timer: 2000,
                showConfirmButton: false,
            });

            datatable.ajax.reload();
        } catch (e) {
            Swal.fire(
                "Error",
                e.responseJSON?.message || "Gagal approve",
                "error"
            );
        } finally {
            submitBtn
                .attr("disabled", false)
                .html('<i class="fas fa-check me-1"></i>Ya, Approve');
        }
    });

    // Submit Reject
    $("#form-reject").on("submit", async function (e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn
            .attr("disabled", true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');

        try {
            const response = await $.ajax({
                url: `/approval/${selectedApprovalId}/reject`,
                method: "POST",
                data: $(this).serialize(),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            });

            bootstrap.Modal.getInstance(
                document.getElementById("reject-modal")
            ).hide();

            Swal.fire({
                icon: "success",
                title: "Rejected!",
                text: response.message,
                timer: 2000,
                showConfirmButton: false,
            });

            datatable.ajax.reload();
        } catch (e) {
            Swal.fire(
                "Error",
                e.responseJSON?.message || "Gagal reject",
                "error"
            );
        } finally {
            submitBtn
                .attr("disabled", false)
                .html('<i class="fas fa-times me-1"></i>Ya, Reject');
            $(this)[0].reset();
        }
    });
});

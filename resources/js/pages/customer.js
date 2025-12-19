$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.split("/").includes("customer")) return;

    const tableElement = $("#customer-table");
    let datatable = null;

    if (tableElement.length > 0) {
        datatable = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ordering: true,
            
            // 🔥 HAPUS PENCARIAN DEFAULT DATATABLES
            searching: false, 
            lengthChange: false, 

            ajax: {
                url: `/customer`,
                type: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: function (d) {
                    d.q = $('#searchInput').val();
                },
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                {
                    data: "id",
                    name: "id",
                    searchable: false,
                    orderable: false,
                    className: "text-center fw-bold text-muted",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: "user", 
                    name: "user_id", 
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function (data, type, row) {
                        return `<img src="${row.user.avatar_url}" class="rounded-circle border shadow-sm" width="45" height="45" style="object-fit: cover;" alt="Avatar">`;
                    }
                },
                {
                    data: "name",
                    name: "name",
                    render: function (data, type, row) {
                        let genderIcon = row.gender === 'Male' ? 'fas fa-mars' : 'fas fa-venus';
                        let genderLabel = row.gender === 'Male' ? 'Laki-laki' : 'Perempuan';
                        
                        return `
                            <span class="d-block fw-bold h6 mb-0" style="color: #50200C">${data}</span>
                            <small class=" " style="color: #50200C">
                                <i class="${genderIcon} me-1"></i> ${genderLabel}
                            </small>
                        `;
                    }
                },
                
                // === [BARU] KOLOM CUSTOMER GROUP ===
                {
                    data: "customer_group",
                    name: "customer_group",
                    className: "text-center",
                    render: function (data) {
                        // Warnai badge sesuai grup
                        let badgeColor = 'secondary'; // Default General
                        if (data === 'Corporate') badgeColor = 'primary';
                        if (data === 'Family') badgeColor = 'success';
                        if (data === 'Government') badgeColor = 'warning text-dark';

                        let text = data ? data : 'General';
                        return `<span class="badge bg-${badgeColor}">${text}</span>`;
                    }
                },
                // ===================================

                {
                    data: "phone",
                    name: "phone",
                    render: function (data, type, row) {
                        let phoneHtml = '<span class="fw-bold" style="color: #50200C">-</span>';
                        
                        if (data) {
                            let waNum = data.replace(/^0/, '62').replace(/[^0-9]/g, '');
                            phoneHtml = `
                                <div class="mb-1" style="color: #50200C">
                                    <a href="https://wa.me/${waNum}" target="_blank" class="text-decoration-none fw-bold" style="color: #50200C">
                                        <i class="fab fa-whatsapp fw-bold me-1"></i> ${data}
                                    </a>
                                </div>`;
                        }
                        
                        let email = (row.user && row.user.email) ? row.user.email : '-';
                        
                        return `
                            ${phoneHtml}
                            <span class="d-block fw-bold" style="color: #50200C">
                                <i class="fas fa-envelope fw-bold me-1"></i> ${email}
                            </span>
                        `;
                    }
                },
                {
                    data: "job",
                    name: "job",
                    render: function (data) {
                        return `<span class="badge bg-light border" style="color: #50200C">${data || '-'}</span>`;
                    }
                },
                {
                    data: "address",
                    name: "address",
                    render: function (data) {
                        return data ? (data.length > 50 ? data.substr(0, 50) + '...' : data) : '-';
                    }
                },
                {
                    data: "id",
                    name: "id",
                    orderable: false,
                    searchable: false,
                    className: "text-center text-nowrap",
                    render: function (id, type, row) {
                        return `
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-light btn-sm rounded shadow-sm border"
                                    data-action="edit-customer" data-customer-id="${id}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <form class="d-inline delete-customer p-0 m-0" method="POST"
                                    action="/customer/${id}">
                                    <input type="hidden" name="_method" value="DELETE"> 
                                    <button type="submit" class="btn btn-light btn-sm rounded shadow-sm border delete"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Data">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>

                                <a class="btn btn-light btn-sm rounded shadow-sm border"
                                    href="/customer/${id}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Customer">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                            </div>
                        `;
                    },
                },
            ],
        });
    }

    // --- Helper Modal ---
    function getModal() {
        var modalEl = document.getElementById('main-modal') || document.getElementById('imageModal');
        if (!modalEl) return null;
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true, focus: true });
        }
        return modalInstance;
    }

    // --- Event Handler ---

    // 1. [BARU] Klik Tombol Tambah Customer
    $(document).on("click", "#add-button", async function () {
        const modal = getModal();
        if (!modal) return;

        modal.show();
        
        // Reset State
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $("#main-modal .modal-title").text("Tambah Tamu Baru"); // Judul Tambah
        $("#main-modal .modal-body").html(`<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary"></div></div>`);
        
        try {
            // Panggil Route Create
            const response = await $.get(`/customer/create`);
            if (response) {
                let content = response.view ? response.view : response;
                $("#main-modal .modal-body").html(content);
                
                // Init Select2 di dalam Modal (Penting untuk Dropdown Grup)
                if($.fn.select2) { 
                    $(".select2").select2({ dropdownParent: $('#main-modal'), width: '100%' }); 
                }
            }
        } catch (error) {
            $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat form.</div>`);
        }
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
    });

    // 2. Klik Tombol Edit
    $(document).on("click", '[data-action="edit-customer"]', async function () {
        const modal = getModal();
        if (!modal) return;

        modal.show();
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $("#main-modal .modal-title").text("Edit Data Customer");
        $("#main-modal .modal-body").html(`<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary"></div></div>`);
        
        try {
            const customerId = $(this).data("customer-id");
            const response = await $.get(`/customer/${customerId}/edit`);
            if (response) {
                let content = response.view ? response.view : response;
                $("#main-modal .modal-body").html(content);
                if($.fn.select2) { $(".select2").select2({ dropdownParent: $('#main-modal'), width: '100%' }); }
            }
        } catch (error) {
            $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat data.</div>`);
        }
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
    });

    // 3. Klik Tombol Simpan
    $(document).on("click", "#btn-modal-save", function () { $("#main-modal form").submit(); });

    // 4. Submit Form (Create / Update)
    $(document).on("submit", "#main-modal form", async function (e) {
        e.preventDefault();
        const submitBtn = $("#btn-modal-save");
        submitBtn.attr("disabled", true).text("Menyimpan...");
        $(".is-invalid").removeClass("is-invalid"); $(".error").text(""); 

        try {
            const response = await $.ajax({
                url: $(this).attr("action"),
                data: new FormData(this),
                method: "POST", 
                processData: false, contentType: false,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            });
            if (response) {
                Swal.fire({ position: "center", icon: "success", title: "Berhasil disimpan", showConfirmButton: false, timer: 1500, iconColor: "#50200C", customClass: { title: "swal-title-brown" } });
                getModal().hide();
                if (datatable) datatable.ajax.reload(); else setTimeout(() => window.location.reload(), 1500); 
            }
        } catch (e) {
            if (e.status === 422) {
                let errors = e.responseJSON.errors;
                for (let field in errors) {
                    $(`[name="${field}"]`).addClass('is-invalid');
                    let errorSpan = $(`#error_${field}`);
                    if(!errorSpan.length) errorSpan = $(`[name="${field}"]`).next('.invalid-feedback');
                    if(errorSpan.length) errorSpan.text(errors[field][0]);
                }
                Swal.fire({ icon: "error", title: "Validasi Gagal", text: "Mohon periksa inputan Anda.", iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
            } else {
                Swal.fire({ icon: "error", title: "Error", text: "Gagal menyimpan data.", iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
            }
        } finally {
            submitBtn.attr("disabled", false).text("Simpan");
        }
    });

    // 5. Delete Customer
    $(document).on("submit", ".delete-customer", async function (e) {
        e.preventDefault(); 
        const result = await Swal.fire({
            title: "Yakin ingin menghapus?",
            text: "Data ini tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#F2C2B8",
            cancelButtonColor: "#8FB8E1",
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal",
            customClass: {
                confirmButton: "text-50200C",
                cancelButton: "text-50200C",
            },
        });
        if (!result.isConfirmed) return;

        try {
            await $.ajax({
                url: $(this).attr("action"), data: $(this).serialize(), method: "POST", 
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            });
            Swal.fire({ position: "center", icon: "success", title: "Data berhasil dihapus", showConfirmButton: false, timer: 1500, iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
            if(datatable) datatable.ajax.reload(); else window.location.href = '/customer'; 
        } catch (e) {
            Swal.fire({
                title: "Gagal",
                text: e.responseJSON?.message || "Gagal menghapus data.",
                icon: "error",
                iconColor: '#50200C',
                customClass: { title: 'swal-title-brown' }
            });
        }
    });

    // 6. Search Trigger (Debounce)
    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    $(document).on("keyup", '#searchInput', debounce(function () {
        if(datatable) datatable.ajax.reload();
    }, 500));
});
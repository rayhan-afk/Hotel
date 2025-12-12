$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("ruangrapat")) return;

    console.log("Ruang Rapat JS Loaded");

    // =========================================================================
    // BAGIAN 1: MANAJEMEN PAKET RUANG RAPAT (DATATABLES BAWAH)
    // =========================================================================
    
    // Inisialisasi DataTable Paket
    const datatable = $("#ruangrapat-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/ruangrapat",
            type: "GET",
            error: function (xhr, status, error) {
                console.error("Error fetching paket data:", error);
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
                searchable: false
            },
            { name: "name", data: "name" },
            { name: "isi_paket", data: "isi_paket" },
            { name: "fasilitas", data: "fasilitas" },
            {
                name: "harga",
                data: "harga",
                render: function (harga) {
                    if (!harga) return "Rp 0";
                    const numericValue = harga.toString().replace(/[^0-9]/g, "");
                    const formatted = parseInt(numericValue, 10) || 0;
                    return `Rp ${new Intl.NumberFormat("id-ID").format(formatted)}`;
                },
            },
            {
                name: "id",
                data: "id",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <button class="btn btn-light btn-sm rounded shadow-sm border me-1"
                            data-action="edit-ruangrapat" data-id="${id}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Paket">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <form class="delete-ruangrapat d-inline-block" method="POST"
                            id="delete-ruangrapat-form-${id}"
                            action="/ruangrapat/${id}">
                            
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                            <input type="hidden" name="_method" value="DELETE">
                            
                            <button type="button" class="btn btn-light btn-sm rounded shadow-sm border delete-paket"
                                data-id="${id}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Hapus Paket">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    `;
                },
            },
        ],
        order: [[1, 'asc']],
        language: {
            emptyTable: "Tidak ada paket ruang rapat yang tersedia saat ini.",
            processing: "Memuat data...",
            zeroRecords: "Data tidak ditemukan"
        }
    });

    const modal = new bootstrap.Modal($("#main-modal"), {
        backdrop: true, keyboard: true, focus: true,
    });

    // =========================================================================
    // BAGIAN 2: EVENT LISTENER GLOBAL
    // =========================================================================

    $(document)
        // --- 2.1. Tombol Hapus Paket (Tabel Bawah) ---
        .on("click", ".delete-paket", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
           Swal.fire({
                title: "Yakin ingin menghapus?",
                text: "Data tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#F2C2B8",
                cancelButtonColor: "#8FB8E1",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    title: 'swal2-title-custom', 
                    html: 'swal2-html-custom',
                    popup: 'swal2-popup-custom',

                    confirmButton: 'text-50200C',
                    cancelButton: 'text-50200C'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#delete-ruangrapat-form-${id}`).trigger('submit');
                }
            });
        })

        // --- 2.2. Submit Form Hapus Paket ---
        .on("submit", ".delete-ruangrapat", async function (e) {
            e.preventDefault();
            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    method: "POST",
                });
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.message || "Data berhasil dihapus!",
                    showConfirmButton: false,
                    timer: 1500,
                    iconColor: "#50200C",
                    customClass: {
                        title: "swal-title-brown"
                    }
                });
                datatable.ajax.reload();
            } catch (e) {
                Swal.fire("Gagal", e.responseJSON?.message || "Tidak dapat menghapus.", "error");
            }
        })

        // --- 2.3. Tambah Paket (Buka Modal) ---
        .on("click", "#add-button", async function (e) {
            e.preventDefault();
            modal.show();
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $("#main-modal .modal-title").text("Tambah Paket Ruang Rapat");
            $("#main-modal .modal-body").html(`<div class="text-center my-5"><span class="spinner-border text-primary"></span><br>Loading...</div>`);

            try {
                const response = await $.get(`/ruangrapat/create`);
                if (response && response.view) {
                    $("#main-modal .modal-body").html(response.view);
                } else {
                    $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat form.</div>`);
                }
                $("#btn-modal-save").text("Simpan").attr("disabled", false);
            } catch (error) {
                $("#main-modal .modal-body").html(`<div class="alert alert-danger">Terjadi kesalahan jaringan.</div>`);
            }
        })

        // --- 2.4. Edit Paket (Buka Modal) ---
        .on("click", '[data-action="edit-ruangrapat"]', async function () {
            modal.show();
            const id = $(this).data("id");
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $("#main-modal .modal-title").text("Edit Paket Ruang Rapat");
            $("#main-modal .modal-body").html(`<div class="text-center my-5"><span class="spinner-border text-primary"></span><br>Loading data...</div>`);

            try {
                const response = await $.get(`/ruangrapat/${id}/edit`);
                if (response && response.view) {
                    $("#main-modal .modal-body").html(response.view);
                } else {
                    $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat data.</div>`);
                }
                $("#btn-modal-save").text("Simpan").attr("disabled", false);
            } catch (error) {
                $("#main-modal .modal-body").html(`<div class="alert alert-danger">Terjadi kesalahan.</div>`);
            }
        })

        // --- 2.5. Simpan Data Paket ---
        .on("click", "#btn-modal-save", function () {
            $("#form-save-ruangrapat").submit();
        })
        .on("submit", "#form-save-ruangrapat", async function (e) {
            e.preventDefault();
            if (typeof CustomHelper !== 'undefined') CustomHelper.clearError();
            
            $("#btn-modal-save").attr("disabled", true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    method: $(this).attr("method"),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                });

                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.message || "Berhasil disimpan!",
                    showConfirmButton: false,
                    timer: 1500,
                    iconColor: "#50200C",
                    customClass: {
                        title: "swal-title-brown"
                    }
                });

                modal.hide();
                datatable.ajax.reload();
            } catch (e) {
                if (e.status === 422) {
                    if (typeof CustomHelper !== 'undefined') {
                        CustomHelper.errorHandlerForm(e);
                    } else {
                        alert("Validasi gagal. Cek inputan Anda.");
                    }
                } else {
                    Swal.fire("Error", e.responseJSON?.message || "Terjadi kesalahan server.", "error");
                }
            } finally {
                $("#btn-modal-save").attr("disabled", false).text('Simpan');
            }
        })

        // =========================================================================
        // BAGIAN 3: MANAJEMEN RESERVASI (TABEL ATAS)
        // =========================================================================

        // --- 3.2. Tombol Hapus Reservasi (Modal Merah) ---
        .on('click', '.delete-btn', function() {
            var name = $(this).data('name');
            var route = $(this).data('route');
            
            $('#deleteItemName').text(name);
            $('#deleteForm').attr('action', route);
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
});
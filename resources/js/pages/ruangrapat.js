$(function () {
    // Cek apakah sedang berada di route yang benar
    const currentRoute = window.location.pathname;
    if (!currentRoute.split("/").includes("ruangrapat")) return;

    // Inisialisasi DataTable
    const datatable = $("#ruangrapat-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `/ruangrapat`,
            type: "GET",
            data: function (d) {
                // Tambahkan parameter filter di sini jika nanti diperlukan
                // d.filter_nama = $("#filter_nama").val();
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data:", error);
            },
        },
       columns: [
            {
                // Kolom Nomor Urut
                name: "id",
                data: "id",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false
            },
            {
                name: "name",
                data: "name",
            },
            {
                // -- DIPERBAIKI --
                // Fungsi render dihapus agar teks tampil penuh
                name: "isi_paket",
                data: "isi_paket",
            },
            {
                // -- DIPERBAIKI --
                // Fungsi render dihapus agar teks tampil penuh
                name: "fasilitas",
                data: "fasilitas",
            },
            {
    name: "harga",
    data: "harga",
    render: function (harga) {
        if (!harga) return "Rp 0";

        // Pastikan harga hanya berisi angka
        const numericValue = harga.toString().replace(/[^0-9]/g, "");

        // Ubah ke integer
        const formatted = parseInt(numericValue, 10) || 0;

        // Format ke rupiah
        return `Rp ${new Intl.NumberFormat("id-ID").format(formatted)}`;
    },
},
            {
                // Kolom Aksi
                name: "id",
                data: "id",
                orderable: false,
                searchable: false,
                render: function (id) {
                    return `
                        <button class="btn btn-light btn-sm rounded shadow-sm border"
                            data-action="edit-ruangrapat" data-id="${id}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Paket">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <form class="delete-ruangrapat" method="POST"
                            id="delete-ruangrapat-form-${id}"
                            action="/ruangrapat/${id}" style="display: inline-block; vertical-align: top;">
                            
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                            <input type="hidden" name="_method" value="DELETE">
                            
                            <a class="btn btn-light btn-sm rounded shadow-sm border delete"
                                href="#" data-id="${id}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Hapus Paket">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </form>
                    `;
                },
            },
        ],
        order: [[1, 'asc']] // Default sort berdasarkan kolom Nama Paket
    });

    // Inisialisasi Modal Bootstrap
    const modal = new bootstrap.Modal($("#main-modal"), {
        backdrop: true,
        keyboard: true,
        focus: true,
    });

    // Event Listener Global
    $(document)
        // --- Tombol Hapus (Tampilkan Konfirmasi SweetAlert) ---
        .on("click", ".delete", function (e) {
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
                    $(`#delete-amenity-form-${id}`).trigger('submit');
                }
            });
        })
        // --- Tombol Tambah Data (Buka Modal) ---
        .on("click", "#add-button", async function () {
            modal.show();
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');

            $("#main-modal .modal-title").text("Tambah Paket Ruang Rapat");
            $("#main-modal .modal-body").html(`<div class="text-center my-5"><span class="spinner-border text-primary"></span><br>Loading...</div>`);

            try {
                const response = await $.get(`/ruangrapat/create`);
                if (response && response.view) {
                    $("#main-modal .modal-body").html(response.view);
                    // Inisialisasi komponen tambahan jika ada (misal: Select2, Summernote)
                    // $('.select2').select2();
                } else {
                     $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat form.</div>`);
                }
                $("#btn-modal-save").text("Simpan").attr("disabled", false);
                $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
                $('.btn-close[data-bs-dismiss="modal"]').text('');
            } catch (error) {
                console.error(error);
                $("#main-modal .modal-body").html(`<div class="alert alert-danger">Terjadi kesalahan jaringan.</div>`);
            }
        })
        // --- Tombol Simpan di Modal ---
        .on("click", "#btn-modal-save", function () {
            // Submit form yang ada di dalam modal
            $("#form-save-ruangrapat").submit();
        })
        // --- Proses Submit Form Simpan/Update ---
        .on("submit", "#form-save-ruangrapat", async function (e) {
            e.preventDefault();
            if (typeof CustomHelper !== 'undefined') CustomHelper.clearError();
            $("#btn-modal-save").attr("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    method: $(this).attr("method"),
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                });

                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: response.message || "Berhasil disimpan!",
                    showConfirmButton: false,
                    timer: 1500,
                });

                modal.hide();
                datatable.ajax.reload(); // Reload tabel
            } catch (e) {
                if (e.status === 422) {
                    // Error validasi Laravel
                    if (typeof CustomHelper !== 'undefined') {
                        CustomHelper.errorHandlerForm(e);
                    } else {
                        // Fallback jika CustomHelper tidak ada
                        alert("Terjadi kesalahan validasi. Periksa kembali inputan Anda.");
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: e.responseJSON?.message || "Terjadi kesalahan pada server.",
                    });
                }
            } finally {
                $("#btn-modal-save").attr("disabled", false).text('Simpan');
            }
        })
        // --- Tombol Edit (Buka Modal Edit) ---
        .on("click", '[data-action="edit-ruangrapat"]', async function () {
            modal.show();
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');

            $("#main-modal .modal-title").text("Edit Paket Ruang Rapat");
            $("#main-modal .modal-body").html(`<div class="text-center my-5"><span class="spinner-border text-primary"></span><br>Loading data...</div>`);

            const id = $(this).data("id");

            try {
                const response = await $.get(`/ruangrapat/${id}/edit`);
                if (response && response.view) {
                    $("#main-modal .modal-body").html(response.view);
                     // Re-inisialisasi komponen jika diperlukan
                } else {
                     $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat data.</div>`);
                }
                $("#btn-modal-save").text("Simpan").attr("disabled", false);
                $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
                $('.btn-close[data-bs-dismiss="modal"]').text('');
            } catch (error) {
                $("#main-modal .modal-body").html(`<div class="alert alert-danger">Terjadi kesalahan saat mengambil data.</div>`);
            }
        })
        // --- Proses Submit Penghapusan ---
        .on("submit", ".delete-ruangrapat", async function (e) {
            e.preventDefault();
            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(), // Akan menyertakan _method=DELETE dan _token
                    method: "POST",
                });

                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: response.message || "Data berhasil dihapus!",
                    showConfirmButton: false,
                    timer: 1500,
                });

                datatable.ajax.reload();
            } catch (e) {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: e.responseJSON?.message || "Tidak dapat menghapus data saat ini.",
                });
            }
        });
});
$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.split("/").includes("room")) return;

    // Inisialisasi Datatable HANYA jika tabelnya ada (di halaman Index)
    const tableElement = $("#room-table");
    let datatable = null;

    if (tableElement.length > 0) {
        datatable = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `/room`,
                type: "GET",
                data: function (d) {
                    // Filter Status Dihapus
                    // d.status = $("#status").val(); 
                    d.type = $("#type").val();
                },
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                { name: "number", data: "number" },
                { name: "name", data: "name" }, // Kolom Baru
                { name: "type", data: "type" },
                { name: "area_sqm", data: "area_sqm", render: function(data) { return data ? data + ' m²' : '-'; } }, // Kolom Baru
                { name: "room_facilities", data: "room_facilities", render: function(data) { return data ? (data.length > 20 ? data.substr(0, 20) + '...' : data) : '-'; } }, // Kolom Baru (Potong teks panjang)
                { name: "bathroom_facilities", data: "bathroom_facilities", render: function(data) { return data ? (data.length > 20 ? data.substr(0, 20) + '...' : data) : '-'; } }, // Kolom Baru (Potong teks panjang)
                { name: "capacity", data: "capacity" },
                {
                    name: "price",
                    data: "price",
                    render: function (price) {
                        return `<div>Rp ${new Intl.NumberFormat('id-ID').format(price)}</div>`;
                    },
                },
                // Kolom Status DIHAPUS dari sini agar sesuai dengan HTML index.blade.php yang baru
                /*
                {
                    name: "status",
                    data: "status",
                    render: function (status) {
                        let badgeClass = 'bg-secondary';
                        if (status === 'Available') badgeClass = 'bg-success';
                        else if (status === 'Occupied') badgeClass = 'bg-danger';
                        else if (status === 'Cleaning') badgeClass = 'bg-warning text-dark';
                        else if (status === 'Reserved') badgeClass = 'bg-info text-dark';
                        
                        return `<span class="badge ${badgeClass}">${status}</span>`;
                    }
                },
                */
                {
                    name: "id",
                    data: "id",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap", // Tambahkan class ini agar tidak wrap
                    render: function (roomId) {
    return `
        <div class="d-flex gap-1">
            <button class="btn btn-light btn-sm rounded shadow-sm border"
                data-action="edit-room" data-room-id="${roomId}"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit room">
                <i class="fas fa-edit"></i>
            </button>
            <form class="d-inline delete-room" method="POST"
                id="delete-room-form-${roomId}"
                action="/room/${roomId}">
                <input type="hidden" name="_method" value="DELETE"> 
                <button type="submit" class="btn btn-light btn-sm rounded shadow-sm border delete"
                    room-id="${roomId}" data-bs-toggle="tooltip"
                    data-bs-placement="top" title="Delete room">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>
            <a class="btn btn-light btn-sm rounded shadow-sm border"
                href="/room/${roomId}"
                data-bs-toggle="tooltip" data-bs-placement="top"
                title="Room detail">
                <i class="fas fa-info-circle"></i>
            </a>
        </div>
    `;
},
                },
            ],
        });
    }

    // Helper Modal
    function getModal() {
        // Coba cari main-modal (di index) atau imageModal (di detail)
        var modalEl = document.getElementById('main-modal') || document.getElementById('imageModal');
        if (!modalEl) return null;
        
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalEl, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }
        return modalInstance;
    }

    // --- Event Handler Umum ---
    
    // 1. Klik Tombol Tambah (Hanya di Index)
    $(document).on("click", "#add-button", async function () {
        const modal = getModal();
        if (!modal) return;

        modal.show();

        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');

        $("#main-modal .modal-title").text("Tambah Kamar Baru");
        
        // KODE FETCHING HTML TETAP SAMA...
        $("#main-modal .modal-body").html(`<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary"></div></div>`);
        try {
            const response = await $.get(`/room/create`);
            if (response && response.view) {
                $("#main-modal .modal-body").html(response.view);
                if($.fn.select2) { $(".select2").select2({ dropdownParent: $('#main-modal'), width: '100%' }); }
            }
        } catch (e) { $("#main-modal .modal-body").html("Error"); }
        
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
    });

    // 2. Klik Tombol Simpan (Di Modal Index)
    $(document).on("click", "#btn-modal-save", function () {
        $("#form-save-room").submit();
    });

    // 3. SUBMIT FORM (Ini yang penting untuk Redirect/Reload)
    // Selector diperluas untuk menangkap form di modal index (#form-save-room) DAN form upload gambar di detail page
    $(document).on("submit", "form[enctype='multipart/form-data'], #form-save-room", async function (e) {
        // Hanya tangkap form yang memang ingin kita handle via AJAX manual
        // Cek apakah form ini punya ID form-save-room atau ada di dalam modal imageModal
        if ($(this).attr('id') !== 'form-save-room' && $(this).closest('#imageModal').length === 0) {
            return; // Biarkan form lain (seperti delete) berjalan normal atau ditangani handler lain
        }

        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"], #btn-modal-save');
        const originalText = submitBtn.text();
        
        submitBtn.attr("disabled", true).text("Saving...");
        $(".is-invalid").removeClass("is-invalid");
        $(".error").text("");

        let formData = new FormData(this);

        try {
            const response = await $.ajax({
                url: form.attr("action"),
                data: formData,
                method: "POST", 
                processData: false,
                contentType: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            if (response) {
              Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.message || "Success",
                    showConfirmButton: false,
                    timer: 1500,
                    iconColor: "#50200C",
                    customClass: {
                        title: "swal-title-brown"
                    }
                });

                const modal = getModal();
                if(modal) modal.hide();
                
                // === LOGIKA REDIRECT/RELOAD ===
                if (datatable) {
                    // Jika ada datatable (Halaman Index), reload tabel saja
                    datatable.ajax.reload();
                } else {
                    // Jika TIDAK ada datatable (Halaman Detail/Show), RELOAD HALAMAN
                    // Ini akan membuat browser me-refresh halaman detail
                    // sehingga gambar baru terlihat.
                    setTimeout(() => {
                        window.location.reload(); 
                    }, 1500); // Tunggu notifikasi Swal selesai
                }
            }
        } catch (e) {
            if (e.status === 422) {
                let errors = e.responseJSON.errors;
                for (let field in errors) {
                    $(`#${field}`).addClass('is-invalid');
                    $(`#error_${field}`).text(errors[field][0]);
                }
                Swal.fire({ icon: "error", title: "Validation Error", text: "Check inputs." });
            } else {
                console.error(e);
                Swal.fire({ 
                    icon: "error", 
                    title: "Error", 
                    text: e.responseJSON?.message || "Failed.",
                    iconColor: '#50200C', // ✅ Warna icon success
                    customClass: {
                        title: 'swal-title-brown' // ✅ Custom warna title
                    }
                });
            }
        } finally {
            submitBtn.attr("disabled", false).text(originalText);
        }
    });

    // 4. Event Delete Room
    $(document).on("submit", ".delete-room", async function (e) {
        e.preventDefault(); // Mencegah refresh halaman

        const form = $(this);
        const url = form.attr("action");

        // Konfirmasi Swal sebelum hapus
        const result = await Swal.fire({
            title: "Yakin ingin menghapus?",
            text: "Data tidak bisa dikembalikan!",
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

        if (!result.isConfirmed) return; // Batal hapus

        try {
            const response = await $.ajax({
                url: url,
                data: form.serialize(),
                method: "POST", // Tetap POST karena ada input _method=DELETE
                headers: { 
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") 
                },
            });
            
            Swal.fire({
                position: "center",
                icon: "success",
                title: response.message || "Berhasil dihapus",
                showConfirmButton: false,
                timer: 1500,
                iconColor: '#50200C', // ✅ Warna icon success
                customClass: {
                    title: 'swal-title-brown' // ✅ Custom warna title
                }
            });
            // Jika di halaman index, reload tabel
            if(datatable) datatable.ajax.reload();
            // Jika di halaman detail, redirect ke index setelah hapus
            else window.location.href = '/room'; 

        } catch (e) {
            console.error(e);
            Swal.fire({ 
                icon: "error", 
                title: "Error", 
                text: e.responseJSON?.message || "Failed to delete room." 
            });
        }
    });

    // 5. Event Edit Room
    $(document).on("click", '[data-action="edit-room"]', async function () {
        const modal = getModal();
        if (!modal) return;

        modal.show();

        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');

        $("#main-modal .modal-title").text("Edit Kamar");
        $("#main-modal .modal-body").html(`
            <div class="d-flex justify-content-center py-5 align-items-center">
                <div class="spinner-border text-primary me-2" role="status"></div>
                <span>Loading data...</span>
            </div>
        `);

        const roomId = $(this).data("room-id");

        try {
            const response = await $.get(`/room/${roomId}/edit`);
            
            if (response && response.view) {
                $("#main-modal .modal-body").html(response.view);
                
                if($.fn.select2) {
                    $(".select2").select2({
                        dropdownParent: $('#main-modal'),
                        width: '100%'
                    });
                }
            } else {
                throw new Error("Invalid response from server");
            }
        } catch (error) {
            console.error("Error loading edit form:", error);
            let msg = "Failed to load data.";
            if(error.responseJSON && error.responseJSON.message) {
                msg += " " + error.responseJSON.message;
            }
            $("#main-modal .modal-body").html(`<div class="alert alert-danger">${msg}</div>`);
        }

        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
    });

    // Filter
    $(document).on("change", "#type", function () {
        if(datatable) datatable.ajax.reload();
    });
});
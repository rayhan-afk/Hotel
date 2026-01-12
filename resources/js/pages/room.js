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
        
        // Reset ukuran modal jika sebelumnya dibesarkan oleh amenities
        $('#main-modal .modal-dialog').removeClass('modal-xl'); 
        $('#main-modal .modal-footer').show();

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
        // JANGAN submit di sini, biarkan handler form yang handle
        // Handler form submit di bawah sudah ada logic loading-nya
        $("#form-save-room").submit();
    });

    // 3. SUBMIT FORM (CORE LOGIC)
    // Selector diperluas: menangkap form tambah kamar (#form-save-room), form upload gambar, DAN FORM AMENITIES (#formBulkAmenities)
    $(document).on("submit", "form[enctype='multipart/form-data'], #form-save-room, #formBulkAmenities", async function (e) {
        
        // Cek target form agar tidak salah tangkap form lain
        if ($(this).attr('id') !== 'form-save-room' && 
            $(this).closest('#imageModal').length === 0 && 
            $(this).attr('id') !== 'formBulkAmenities') { 
            return; 
        }

        e.preventDefault();
        const form = $(this);

        // --- [PERBAIKAN TARGET TOMBOL] ---
        // Kita target LANGSUNG ke ID yang ada di Master Blade
        let submitBtn = $("#btn-modal-save");

        // Pengecekan Tambahan:
        // Jika tombol Master tidak terlihat (misal lagi di halaman Detail/Edit Gambar/Bulk Amenities),
        // baru kita cari tombol yang ada di dalam form tersebut.
        if (submitBtn.length === 0 || !submitBtn.is(":visible")) {
            submitBtn = form.find('button[type="submit"]');
        }

        // Simpan text asli
        const originalText = submitBtn.html(); 
        
        // --- UBAH JADI LOADING ---
        // Pakai .html() untuk memasukkan icon spinner
        submitBtn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');
        
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
                // SUKSES: Ubah jadi hijau & Centang
                submitBtn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Berhasil!');

                Swal.fire({
                    position: "center", icon: "success", title: response.message || "Berhasil",
                    showConfirmButton: false, timer: 1500, iconColor: "#50200C",
                    customClass: { title: "swal-title-brown" }
                });

                const modal = getModal();
                if(modal) modal.hide();
                
                if (datatable) {
                    datatable.ajax.reload();
                    // Reset tombol setelah 1 detik
                    setTimeout(() => {
                        submitBtn.prop("disabled", false).html("Simpan").removeClass('btn-success').addClass('btn-primary');
                    }, 1000);
                } else {
                    setTimeout(() => { window.location.reload(); }, 1500); 
                }
            }
        } catch (e) {
            // ERROR: Balikin tombol
            submitBtn.prop("disabled", false).html(originalText);

            if (e.status === 422) {
                let errors = e.responseJSON.errors;
                for (let field in errors) {
                    $(`#${field}`).addClass('is-invalid');
                    $(`#error_${field}`).text(errors[field][0]);
                }
                Swal.fire({ icon: "error", title: "Validasi Error", text: "Cek kembali inputan anda." });
            } else {
                console.error(e);
                Swal.fire({ icon: "error", title: "Error", text: e.responseJSON?.message || "Gagal menyimpan." });
            }
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
            background: '#F7F3E4',
            showCancelButton: true,
            confirmButtonColor: "#F2C2B8",
            cancelButtonColor: "#8FB8E1",
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal',
            iconColor: '#50200C',
            customClass: {
                confirmButton: "text-50200C",
                cancelButton: "text-50200C",
                title: "text-50200C",
                htmlContainer: "text-50200C"
            }
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
        
        // Reset ukuran modal & footer
        $('#main-modal .modal-dialog').removeClass('modal-xl'); 
        $('#main-modal .modal-footer').show();

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

    // 6. [BARU] Event Klik Tombol Setup Amenities Massal
    $(document).on("click", "#btn-bulk-amenities", async function () {
        const modal = getModal();
        if (!modal) return;

        modal.show();

        // A. Ubah Tampilan Modal
        $("#main-modal .modal-title").html('<i class="fas fa-boxes me-2"></i> Setup Amenities (Per Tipe)');
        
        // Perbesar modal jadi XL khusus fitur ini
        $('#main-modal .modal-dialog').addClass('modal-xl'); 
        
        // Sembunyikan footer bawaan (karena tombol simpan ada di dalam form amenities)
        $('#main-modal .modal-footer').hide();

        // Tampilkan Loading
        $("#main-modal .modal-body").html(`
            <div class="d-flex justify-content-center py-5 align-items-center">
                <div class="spinner-border text-info me-2" role="status"></div>
                <span>Memuat data amenities...</span>
            </div>
        `);

        // B. Ambil Data via AJAX
        try {
            // Panggil route yang sudah kita buat
            const response = await $.get(`/room/setup-amenities`);
            
            if (response) {
                // Masukkan HTML form ke body modal
                $("#main-modal .modal-body").html(response);
            } else {
                throw new Error("Data kosong");
            }
        } catch (error) {
            console.error("Error loading amenities:", error);
            $("#main-modal .modal-body").html(`<div class="alert alert-danger">Gagal memuat data amenities.</div>`);
        }
    });

    // 7. [PENTING] Reset Modal saat ditutup
    // Supaya kalau habis buka Amenities (XL) terus buka Tambah Kamar, ukurannya balik normal
    const modalEl = document.getElementById('main-modal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            $('#main-modal .modal-dialog').removeClass('modal-xl'); // Hapus class XL
            $('#main-modal .modal-footer').show(); // Munculkan footer lagi
        });
    }

    // Filter
    $(document).on("change", "#type", function () {
        if(datatable) datatable.ajax.reload();
    });
});
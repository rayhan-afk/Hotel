$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.split("/").includes("amenity")) return;

    // === TAMBAH CSS ANIMASI UNTUK WARNING ===
    const style = document.createElement("style");
    style.textContent = `
        @keyframes pulseWarning {
            0%, 100% {
                background-color: #ff6b6b;
                box-shadow: 0 0 15px rgba(255, 107, 107, 0.6);
            }
            50% {
                background-color: #ff8787;
                box-shadow: 0 0 25px rgba(255, 107, 107, 0.3);
            }
        }
        
        .low-stock-row {
            animation: pulseWarning 1.2s ease-in-out infinite !important;
            border: 3px solid #ff4757 !important;
            background-color: #ff6b6b !important;
        }
        
        .low-stock-row td {
            color: #ffffff !important;
            font-weight: 700 !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            background-color: transparent !important;
        }
        
        /* Pastikan badge tidak terpengaruh style row */
        .low-stock-row .badge {
            color: inherit !important;
            text-shadow: none !important;
            font-weight: 600 !important;
        }
        
        .low-stock-row .badge.bg-danger {
            background-color: #212529 !important;
            color: #ffffff !important;
        }
        
        .low-stock-row .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000000 !important;
        }
        
        .low-stock-row:hover {
            background-color: #ff4757 !important;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4) !important;
            transition: all 0.3s ease;
        }
        
        .low-stock-icon {
            animation: shake 0.4s ease-in-out infinite;
            filter: drop-shadow(0 0 5px rgba(255,255,255,0.9));
            color: #fff !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0) rotate(0deg); }
            25% { transform: translateX(-5px) rotate(-8deg); }
            50% { transform: translateX(5px) rotate(8deg); }
            75% { transform: translateX(-5px) rotate(-8deg); }
        }
        
        .low-stock-row.odd,
        .low-stock-row.even {
            background-color: #ff6b6b !important;
        }
        
        .low-stock-row .btn-light {
            background-color: rgba(255, 255, 255, 0.9) !important;
        }
    `;
    document.head.appendChild(style);

    // === FUNGSI UNTUK MEMUTAR SUARA WARNING ===
    let hasPlayedWarningSound = false;
    let audioContext = null;

    function playWarningSound() {
        console.log("🔊 Attempting to play warning sound...");

        try {
            if (!audioContext) {
                audioContext = new (window.AudioContext ||
                    window.webkitAudioContext)();
            }

            if (audioContext.state === "suspended") {
                audioContext.resume();
            }

            const beeps = [
                { freq: 900, start: 0, duration: 0.15 },
                { freq: 700, start: 0.25, duration: 0.15 },
                { freq: 900, start: 0.5, duration: 0.2 },
            ];

            beeps.forEach((beep) => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = beep.freq;
                gainNode.gain.setValueAtTime(
                    0,
                    audioContext.currentTime + beep.start
                );
                gainNode.gain.linearRampToValueAtTime(
                    0.3,
                    audioContext.currentTime + beep.start + 0.01
                );
                gainNode.gain.exponentialRampToValueAtTime(
                    0.01,
                    audioContext.currentTime + beep.start + beep.duration
                );

                oscillator.start(audioContext.currentTime + beep.start);
                oscillator.stop(
                    audioContext.currentTime + beep.start + beep.duration
                );
            });

            console.log("✅ Web Audio API sound played!");
        } catch (error) {
            console.error("❌ Web Audio API failed:", error);
        }

        setTimeout(() => {
            try {
                const audio = new Audio(
                    "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTUIGGS56+mdTQ0OTKXh8LJnGwU7k9nyw3QpBSh+zPDijz8KElyw6OyrWBELTKDd8sFuJAUqg87y2ok0CAdefObsqlUSCk+m4e+xaBsFOpPY8sN1KwUofs3w34pBCw9dtunxrV4PDE+h3/K7bycFKYPM8tmJNAgZZrjq6J5QDA5Kp+HwtmocBjiR2PLEeCwGI3fH8N2RQAoVXrTp66hUFQtHnt/yvnAiBSl/zfHaiTQIF2O56+idUAwOTKTh77VpHAU6k9jzxHYtBSh+zPDfjj8LEV6w6e+sWBELTKHe8sBwJQYof8zw24k0CRdkveLpnlUPDkum4PCxaBwFO5PZ88N2LQUmfszw34s/CBNY"
                );
                audio.volume = 0.5;
                audio
                    .play()
                    .catch((e) => console.log("HTML5 Audio blocked:", e));
                console.log("✅ Fallback audio attempted!");
            } catch (error) {
                console.error("❌ Fallback audio failed:", error);
            }
        }, 100);
    }

    function unlockAudio() {
        if (audioContext && audioContext.state === "suspended") {
            audioContext.resume();
        }
        document.removeEventListener("click", unlockAudio);
        document.removeEventListener("touchstart", unlockAudio);
        console.log("🔓 Audio context unlocked!");
    }

    document.addEventListener("click", unlockAudio);
    document.addEventListener("touchstart", unlockAudio);

    const datatable = $("#amenity-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `/amenity`,
            type: "GET",
            error: function (xhr, status, error) {
                console.error("Error fetching data:", error);
            },
            dataSrc: function (json) {
                const lowStockItems = json.aaData.filter(
                    (item) => item.is_low_stock
                );
                const hasLowStock = lowStockItems.length > 0;

                console.log(
                    "📊 Low stock amenities found:",
                    lowStockItems.length
                );

                if (hasLowStock && !hasPlayedWarningSound) {
                    console.log("🚨 Playing warning sound...");
                    playWarningSound();
                    hasPlayedWarningSound = true;

                    if (typeof Swal !== "undefined") {
                        const itemNames = lowStockItems
                            .map((item) => item.nama_barang)
                            .join(", ");
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "warning",
                            title: "🚨 STOK AMENITIES KRITIS!",
                            html: `<strong>${lowStockItems.length} item</strong> hampir habis:<br><small>${itemNames}</small>`,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            background: "#ff6b6b",
                            color: "#fff",
                        });
                    }
                }

                return json.aaData;
            },
        },
        columns: [
            {
                data: "id",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
            },
            {
                name: "nama_barang",
                data: "nama_barang",
                className: "fw-bold text-dark align-middle",
            },
            {
                name: "stok",
                data: "stok",
                className: "text-end pe-4 fw-bold align-middle",
            },
            {
                name: "satuan",
                data: "satuan",
                className: "align-middle",
            },
            {
                name: "stok",
                data: "stok",
                render: function (data) {
                    let stok = parseInt(data);

                    if (stok === 0)
                        return '<span class="badge fs-6" style="background-color: #F2C2B8; color: #50200C !important;"><i class="fas fa-times-circle me-1" style="color: #50200C !important;"></i>HABIS</span>';
                    if (stok < 5)
                        return '<span class="badge fs-6" style="background-color: #FAE8A4; color: #50200C !important;"><i class="fas fa-exclamation-triangle me-1" style="color: #50200C !important;"></i>KRITIS!</span>';
                    if (stok < 20)
                        return '<span class="badge" style="background-color: #F7B267; color: #50200C; font-weight: bold;">Menipis</span>';
                    if (stok > 50)
                        return '<span class="badge" style="background-color: #A8D5BA; color: #50200C; font-weight: bold;">Tersedia</span>';
                    return '<span class="badge" style="background-color: #8FB8E1; color: #50200C; font-weight: bold;">Cukup</span>';
                },
                className: "align-middle",
            },
            {
                name: "keterangan",
                data: "keterangan",
                className: "align-middle",
            },
            {
                name: "id",
                data: "id",
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
                render: function (id) {
                    return `
                        <button class="btn btn-sm btn-light border text-primary shadow-sm me-1" 
                            data-action="edit-amenity" data-id="${id}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form class="btn btn-sm delete-amenity" method="POST"
                            id="delete-amenity-form-${id}"
                            action="/amenity/${id}" style="display:inline; padding:0;">
                            <a class="btn btn-light btn-sm rounded shadow-sm border delete"
                                href="#" data-id="${id}" data-bs-toggle="tooltip"
                                data-bs-placement="top" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </form>
                    `;
                },
            },
        ],
        createdRow: function (row, data, dataIndex) {
            if (data.is_low_stock) {
                $(row).addClass("low-stock-row");
                $(row)
                    .find("td:eq(1)")
                    .prepend(
                        '<i class="fas fa-exclamation-triangle me-2 low-stock-icon" style="background-color: red !important;"></i>'
                    );
                $(row).attr(
                    "title",
                    "🚨 PERINGATAN KRITIS: Stok amenities hampir habis! Segera lakukan pembelian ulang."
                );
            }
        },
        drawCallback: function () {
            $(".low-stock-row").each(function () {
                $(this).css("background-color", "#ff6b6b");
            });
        },
        language: {
            emptyTable: "Tidak ada data amenities saat ini.",
            processing: "Memuat data...",
            zeroRecords: "Data tidak ditemukan"
        }
    });

    // --- LOGIKA MODAL & BUTTONS ---

    const modal = new bootstrap.Modal($("#main-modal"), {
        backdrop: true,
        keyboard: true,
        focus: true,
    });

    // $('[data-bs-dismiss="modal"]').text("Batal");
    $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
    $('.btn-close[data-bs-dismiss="modal"]').text('');

    $("#main-modal").on('hidden.bs.modal', function () {
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $("button[data-bs-dismiss=modal]").text("Batal");
        $(".btn-close[data-bs-dismiss=modal]").text('');
        $("#main-modal .modal-body").html("");
    });

    $("#main-modal").on("show.bs.modal", function () {
        $("#main-modal .modal-title").text("");              
        $("#main-modal .modal-body").html("Fetching data...");
    });

    $(document)
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
                    confirmButton: "text-50200C",
                    cancelButton: "text-50200C",
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#delete-amenity-form-${id}`).trigger("submit");
                }
            });
        })
        .on("click", "#add-button", async function () {
            modal.show();
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');

            $("#main-modal .modal-body").html(`Fetching data...`);

            const response = await $.get(`/amenity/create`);
            $("#main-modal .modal-title").text("Tambah Amenities");
            $("#main-modal .modal-body").html(response.view);

            $("#btn-modal-save").text("Simpan").attr("disabled", false);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');
        })
        .on("click", '[data-action="edit-amenity"]', async function () {
            modal.show();
            $("#btn-modal-save").text("Simpan").attr("disabled", true);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');

            $("#main-modal .modal-body").html(`Fetching data...`);

            const id = $(this).data("id");
            const response = await $.get(`/amenity/${id}/edit`);

            $("#main-modal .modal-title").text("Edit Amenities");
            $("#main-modal .modal-body").html(response.view);

            $("#btn-modal-save").text("Simpan").attr("disabled", false);
            $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
            $('.btn-close[data-bs-dismiss="modal"]').text('');
        })
        .on("click", "#btn-modal-save", function () {
            $("#form-save-amenity").submit();
        })
        .on("submit", "#form-save-amenity", async function (e) {
            e.preventDefault();
            if (typeof CustomHelper !== "undefined") CustomHelper.clearError();
            $("#btn-modal-save").attr("disabled", true).text("Menyimpan...");

            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    method: $(this).attr("method"),
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                });

                Swal.fire({
                    icon: "success",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500,
                });
                modal.hide();
                hasPlayedWarningSound = false;
                datatable.ajax.reload();
            } catch (e) {
                if (e.status === 422 && typeof CustomHelper !== "undefined") {
                    CustomHelper.errorHandlerForm(e);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Something went wrong!",
                    });
                }
            } finally {
                $("#btn-modal-save").attr("disabled", false).text("Simpan");
            }
        })
        .on("submit", ".delete-amenity", async function (e) {
            e.preventDefault();
            try {
                const response = await $.ajax({
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                });
                Swal.fire({
                    icon: "success",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500,
                });
                hasPlayedWarningSound = false;
                datatable.ajax.reload();
            } catch (e) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to delete data.",
                });
            }
        });
});

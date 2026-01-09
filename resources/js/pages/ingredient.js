$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("ingredient")) return;

    console.log("🚀 Ingredient JS Loaded - Fixed Version");

    // === TAMBAH CSS ANIMASI UNTUK WARNING ===
    const style = document.createElement('style');
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
        
        .low-stock-row .badge {
            color: inherit !important;
            text-shadow: none !important;
            font-weight: 600 !important;
        }
        
        .low-stock-row .badge.bg-dark {
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
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }
            
            const beeps = [
                { freq: 900, start: 0, duration: 0.15 },
                { freq: 700, start: 0.25, duration: 0.15 },
                { freq: 900, start: 0.5, duration: 0.2 }
            ];
            
            beeps.forEach(beep => {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = beep.freq;
                gainNode.gain.setValueAtTime(0, audioContext.currentTime + beep.start);
                gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + beep.start + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + beep.start + beep.duration);
                
                oscillator.start(audioContext.currentTime + beep.start);
                oscillator.stop(audioContext.currentTime + beep.start + beep.duration);
            });
            
            console.log("✅ Web Audio API sound played!");
        } catch (error) {
            console.error("❌ Web Audio API failed:", error);
        }
        
        setTimeout(() => {
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTUIGGS56+mdTQ0OTKXh8LJnGwU7k9nyw3QpBSh+zPDijz8KElyw6OyrWBELTKDd8sFuJAUqg87y2ok0CAdefObsqlUSCk+m4e+xaBsFOpPY8sN1KwUofs3w34pBCw9dtunxrV4PDE+h3/K7bycFKYPM8tmJNAgZZrjq6J5QDA5Kp+HwtmocBjiR2PLEeCwGI3fH8N2RQAoVXrTp66hUFQtHnt/yvnAiBSl/zfHaiTQIF2O56+idUAwOTKTh77VpHAU6k9jzxHYtBSh+zPDfjj8LEV6w6e+sWBELTKHe8sBwJQYof8zw24k0CRdkveLpnlUPDkum4PCxaBwFO5PZ88N2LQUmfszw34s/CBNY');
                audio.volume = 0.5;
                audio.play().catch(e => console.log("HTML5 Audio blocked:", e));
                console.log("✅ Fallback audio attempted!");
            } catch (error) {
                console.error("❌ Fallback audio failed:", error);
            }
        }, 100);
    }
    
    function unlockAudio() {
        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume();
        }
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('touchstart', unlockAudio);
        console.log("🔓 Audio context unlocked!");
    }
    
    document.addEventListener('click', unlockAudio);
    document.addEventListener('touchstart', unlockAudio);

    // ✅ CRITICAL FIX: Gunakan ID modal yang BENAR (#ingredient-modal, bukan #main-modal)
    const ingredientModal = new bootstrap.Modal(document.getElementById("ingredient-modal"));
    const stockOpnameModal = new bootstrap.Modal(document.getElementById("modalStockOpname"));
    const laporanModal = new bootstrap.Modal(document.getElementById("modalLaporanIngredients"));

    console.log("✅ Modals initialized:", {
        ingredient: $('#ingredient-modal').length > 0,
        stockOpname: $('#modalStockOpname').length > 0,
        laporan: $('#modalLaporanIngredients').length > 0
    });

    // ✅ Reset ingredient modal ketika ditutup
    $('#ingredient-modal').on('hidden.bs.modal', function () {
        console.log("🧹 Ingredient modal closed - resetting");
        $(this).find('.modal-body').html('');
        $(this).find('.modal-title').text('Form');
        $('#btn-modal-save').off('click');
    });

    // ✅ CRITICAL: Handler Stock Opname Button
    $('#btn-stock-opname').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        console.log("📋 ========== STOCK OPNAME CLICKED ==========");
        
        // Tutup modal ingredient
        ingredientModal.hide();
        laporanModal.hide();
        
        // Tunggu, lalu buka Stock Opname
        setTimeout(() => {
            console.log("✅ Opening Stock Opname modal");
            stockOpnameModal.show();
        }, 400);
        
        return false;
    });

    // ✅ CRITICAL: Handler Laporan Button
    $('#btn-laporan').on('click', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        console.log("📄 ========== LAPORAN CLICKED ==========");
        
        // Tutup modal ingredient
        ingredientModal.hide();
        stockOpnameModal.hide();
        
        // Tunggu, lalu buka Laporan
        setTimeout(() => {
            console.log("✅ Opening Laporan modal");
            laporanModal.show();
        }, 400);
        
        return false;
    });

    const datatable = $("#ingredient-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/ingredient",
            type: "GET",
            data: function (d) {
                d.category = $("#category_filter").val();
                console.log("Sedang memfilter kategori:", d.category);
            },
            error: function (xhr) {
                console.error("Error:", xhr);
            },
            dataSrc: function (json) {
                const lowStockItems = json.aaData.filter(item => item.is_low_stock);
                const hasLowStock = lowStockItems.length > 0;
                
                console.log("📊 Low stock items found:", lowStockItems.length);
                
                if (hasLowStock && !hasPlayedWarningSound) {
                    console.log("🚨 Playing warning sound...");
                    playWarningSound();
                    hasPlayedWarningSound = true;
                    
                    if (typeof Swal !== 'undefined') {
                        const itemNames = lowStockItems.map(item => item.name).join(', ');
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: '🚨 STOK KRITIS!',
                            html: `<strong>${lowStockItems.length} bahan</strong> hampir habis:<br><small>${itemNames}</small>`,
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                            background: '#ff6b6b',
                            color: '#fff',
                        });
                    }
                }
                
                return json.aaData;
            }
        },
        columns: [
            {
                data: "id",
                render: (data, type, row, meta) =>
                    meta.row + meta.settings._iDisplayStart + 1,
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
            },
            {
                name: "name",
                data: "name",
                className: "fw-bold align-middle",
            },
            {
                name: "category",
                data: "category",
                className: "align-middle",
            },
            {
                name: "stock",
                data: "stock",
                className: "text-end pe-4 fw-bold align-middle",
            },
            {
                name: "unit",
                data: "unit",
                className: "align-middle",
            },
            {
                name: "stock",
                data: "stock",
                render: function (data) {
                    let stok = parseFloat(data);
                    if (stok === 0)
                        return '<span class="badge fs-6" style="background-color: #F2C2B8; color: #50200C !important;"><i class="fas fa-times-circle me-1" style="color: #50200C !important;"></i>HABIS</span>';
                    if (stok < 5)
                        return '<span class="badge fs-6" style="background-color: #FAE8A4; color: #50200C !important;"><i class="fas fa-exclamation-triangle me-1" style="color: #50200C !important;"></i>KRITIS!</span>';
                    if (stok < 20)
                        return '<span class="badge" style="background-color: #F7B267; color: #50200C; font-weight: bold;">Menipis</span>';
                    if (stok > 21)
                        return '<span class="badge" style="background-color: #A8D5BA; color: #50200C; font-weight: bold;">Tersedia</span>';
                    return '<span class="badge" style="background-color: #8FB8E1; color: #50200C; font-weight: bold;">Cukup</span>';
                },
                className: "align-middle",
            },
            {
                name: "description",
                data: "description",
                className: "align-middle",
            },
            {
                data: "id",
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
                render: function (id) {
                    return `
                        <button class="btn btn-sm btn-light border text-primary shadow-sm me-1" 
                            data-action="edit" data-id="${id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-light border text-danger shadow-sm delete-btn" 
                            data-id="${id}" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        <form id="delete-form-${id}" action="/ingredient/${id}" method="POST" style="display:none;">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr("content")}">
                            <input type="hidden" name="_method" value="DELETE">
                        </form>
                    `;
                },
            },
        ],
        createdRow: function (row, data, dataIndex) {
            if (data.is_low_stock) {
                $(row).addClass('low-stock-row');
                $(row).find('td:eq(1)').prepend(
                    '<i class="fas fa-exclamation-triangle me-2 low-stock-icon" style="background-color: red !important;"></i>'
                );
                $(row).attr('title', '🚨 PERINGATAN KRITIS: Stok hampir habis! Segera lakukan pembelian ulang.');
            }
        },
        drawCallback: function() {
            $('.low-stock-row').each(function() {
                $(this).css('background-color', '#ff6b6b');
            });
        },
        language: {
            emptyTable: "Tidak ada data bahan baku saat ini.",
            processing: "Memuat data...",
            zeroRecords: "Data tidak ditemukan"
        }
    });

    $("#category_filter").on("change", function () {
        hasPlayedWarningSound = false;
        datatable.ajax.reload();
    });

    // ✅ Handler Tambah Bahan Baku
    $("#add-button").on("click", async function () {
        console.log("➕ ========== ADD BUTTON CLICKED ==========");
        
        // Tutup modal lain
        stockOpnameModal.hide();
        laporanModal.hide();
        
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');

        $("#ingredient-modal .modal-title").text("Tambah Bahan Baku");
        $("#ingredient-modal .modal-body").html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
        
        setTimeout(() => {
            ingredientModal.show();
        }, 400);
        
        try {
            const response = await $.get("/ingredient/create");
            $("#ingredient-modal .modal-body").html(response.view);
        } catch (e) {
            $("#ingredient-modal .modal-body").html('<div class="text-danger">Gagal memuat form.</div>');
        }

        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
    });

    // ✅ Handler Edit Bahan Baku
    $(document).on("click", '[data-action="edit"]', async function () {
        console.log("✏️ ========== EDIT BUTTON CLICKED ==========");
        
        // Tutup modal lain
        stockOpnameModal.hide();
        laporanModal.hide();
        
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');

        const id = $(this).data("id");
        $("#ingredient-modal .modal-title").text("Edit Bahan Baku");
        $("#ingredient-modal .modal-body").html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
        
        setTimeout(() => {
            ingredientModal.show();
        }, 400);
        
        try {
            const response = await $.get(`/ingredient/${id}/edit`);
            $("#ingredient-modal .modal-body").html(response.view);
        } catch (e) {
            $("#ingredient-modal .modal-body").html('<div class="text-danger">Gagal memuat data.</div>');
        }

        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
    });

    $("#btn-modal-save").on("click", function () {
        $("#form-save-ingredient").submit();
    });

    $(document).on("submit", "#form-save-ingredient", async function (e) {
        e.preventDefault();
        $(".is-invalid").removeClass("is-invalid");
        $(".error").text("");

        let btnSave = $("#btn-modal-save");
        let originalText = btnSave.text();
        btnSave.attr("disabled", true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        try {
            const response = await $.ajax({
                url: $(this).attr("action"),
                method: $(this).attr("method"),
                data: $(this).serialize(),
            });

            ingredientModal.hide();
            
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: response.message,
                timer: 1500,
                showConfirmButton: false,
                iconColor: '#50200C',
                customClass: {
                    title: 'swal-title-brown'
                }
            });
            hasPlayedWarningSound = false;
            datatable.ajax.reload();
        } catch (error) {
            if (error.status === 422) {
                const errors = error.responseJSON.errors;
                for (const [field, messages] of Object.entries(errors)) {
                    $(`#error_${field}`).text(messages[0]);
                    $(`[name="${field}"]`).addClass("is-invalid");
                }
            } else {
               Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Terjadi kesalahan!",
                    iconColor: '#50200C',
                    customClass: {
                        title: 'swal-title-brown'
                    }
                });
            }
        } finally {
            btnSave.attr("disabled", false).text(originalText);
        }
    });

    $(document).on("click", ".delete-btn", function () {
        const id = $(this).data("id");
        Swal.fire({
            title: "Hapus bahan ini?",
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
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await $.ajax({
                        url: $(`#delete-form-${id}`).attr("action"),
                        method: "POST",
                        data: $(`#delete-form-${id}`).serialize(),
                    });
                   Swal.fire({
                        title: "Terhapus!",
                        text: "Data bahan baku berhasil dihapus.",
                        icon: "success",
                        iconColor: '#50200C',
                        customClass: {
                            title: 'swal-title-brown'
                        }
                    });
                    hasPlayedWarningSound = false;
                    datatable.ajax.reload();
                } catch (e) {
                   Swal.fire({
                        title: "Gagal",
                        text: "Gagal menghapus data.",
                        icon: "error",
                        iconColor: '#50200C',
                        customClass: {
                            title: 'swal-title-brown'
                        }
                    });
                }
            }
        });
    });
});
$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("ingredient")) return;

    console.log("🚀 Ingredient JS Loaded - Final Fix for Save Button");

    // ================================================================
    // 1. STYLE & AUDIO (TIDAK BERUBAH)
    // ================================================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulseWarning { 0%, 100% { background-color: #ff6b6b; box-shadow: 0 0 15px rgba(255, 107, 107, 0.6); } 50% { background-color: #ff8787; box-shadow: 0 0 25px rgba(255, 107, 107, 0.3); } }
        .low-stock-row { animation: pulseWarning 1.2s ease-in-out infinite !important; border: 3px solid #ff4757 !important; background-color: #ff6b6b !important; }
        .low-stock-row td { color: #ffffff !important; font-weight: 700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); background-color: transparent !important; }
        .low-stock-row .badge { color: inherit !important; font-weight: 600 !important; }
        .low-stock-row .badge.bg-dark { background-color: #212529 !important; color: #ffffff !important; }
        .low-stock-row .badge.bg-warning { background-color: #ffc107 !important; color: #000000 !important; }
        .low-stock-row:hover { background-color: #ff4757 !important; transform: scale(1.02); box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4) !important; transition: all 0.3s ease; }
        .low-stock-icon { animation: shake 0.4s ease-in-out infinite; filter: drop-shadow(0 0 5px rgba(255,255,255,0.9)); color: #fff !important; }
        @keyframes shake { 0%, 100% { transform: translateX(0) rotate(0deg); } 25% { transform: translateX(-5px) rotate(-8deg); } 75% { transform: translateX(-5px) rotate(-8deg); } }
        .low-stock-row.odd, .low-stock-row.even { background-color: #ff6b6b !important; }
        .low-stock-row .btn-light { background-color: rgba(255, 255, 255, 0.9) !important; }
    `;
    document.head.appendChild(style);

    let hasPlayedWarningSound = false;
    let audioContext = null;

    function playWarningSound() {
        try {
            if (!audioContext) audioContext = new (window.AudioContext || window.webkitAudioContext)();
            if (audioContext.state === 'suspended') audioContext.resume();
            const beeps = [{freq:900,s:0,d:0.15}, {freq:700,s:0.25,d:0.15}, {freq:900,s:0.5,d:0.2}];
            beeps.forEach(b => {
                const o = audioContext.createOscillator(), g = audioContext.createGain();
                o.connect(g); g.connect(audioContext.destination);
                o.frequency.value = b.freq;
                g.gain.setValueAtTime(0, audioContext.currentTime + b.s);
                g.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + b.s + 0.01);
                g.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + b.s + b.d);
                o.start(audioContext.currentTime + b.s); o.stop(audioContext.currentTime + b.s + b.d);
            });
        } catch (e) { console.error("Audio Error", e); }
        setTimeout(() => { try { const a = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTUIGGS56+mdTQ0OTKXh8LJnGwU7k9nyw3QpBSh+zPDijz8KElyw6OyrWBELTKDd8sFuJAUqg87y2ok0CAdefObsqlUSCk+m4e+xaBsFOpPY8sN1KwUofs3w34pBCw9dtunxrV4PDE+h3/K7bycFKYPM8tmJNAgZZrjq6J5QDA5Kp+HwtmocBjiR2PLEeCwGI3fH8N2RQAoVXrTp66hUFQtHnt/yvnAiBSl/zfHaiTQIF2O56+idUAwOTKTh77VpHAU6k9jzxHYtBSh+zPDfjj8LEV6w6e+sWBELTKHe8sBwJQYof8zw24k0CRdkveLpnlUPDkum4PCxaBwFO5PZ88N2LQUmfszw34s/CBNY'); a.volume = 0.5; a.play().catch(e => {}); } catch (e) {} }, 100);
    }

    function unlockAudio() {
        if (audioContext && audioContext.state === 'suspended') audioContext.resume();
        document.removeEventListener('click', unlockAudio);
    }
    document.addEventListener('click', unlockAudio);

    // ================================================================
    // 2. MODAL & LOGIKA PENGAMANAN (UTAMA)
    // ================================================================

    const ingredientModal = new bootstrap.Modal(document.getElementById("ingredient-modal"));
    const stockOpnameModal = new bootstrap.Modal(document.getElementById("modalStockOpname"));
    const laporanModal = new bootstrap.Modal(document.getElementById("modalLaporanIngredients"));

    // ✅ PEMBERSIH & RESETTER (Fix Tombol Mati)
    $('#ingredient-modal').on('hidden.bs.modal', function () {
        console.log("🧹 Ingredient modal closed - resetting form");
        $(this).find('.modal-body').html(''); // Kosongkan isi
        $(this).find('.modal-title').text('Form');
        
        // Pastikan tombol hidup kembali dan teksnya benar
        $('#btn-modal-save').text("Simpan").attr("disabled", false);
        
        $(".is-invalid").removeClass("is-invalid");
        $(".error").text("");
    });

    // ✅ Handler Stock Opname (Fix Konflik)
    $('#btn-stock-opname').on('click', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        ingredientModal.hide(); laporanModal.hide();
        setTimeout(() => { stockOpnameModal.show(); }, 400);
        return false;
    });

    // ✅ Handler Laporan (Fix Konflik)
    $('#btn-laporan').on('click', function(e) {
        e.preventDefault(); e.stopImmediatePropagation();
        ingredientModal.hide(); stockOpnameModal.hide();
        setTimeout(() => { laporanModal.show(); }, 400);
        return false;
    });

    // ================================================================
    // 3. DATATABLE
    // ================================================================
    const datatable = $("#ingredient-table").DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: "/ingredient", type: "GET",
            data: function (d) { d.category = $("#category_filter").val(); },
            dataSrc: function (json) {
                const lowStockItems = json.aaData.filter(item => item.is_low_stock);
                if (lowStockItems.length > 0 && !hasPlayedWarningSound) {
                    playWarningSound(); hasPlayedWarningSound = true;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'warning', title: '🚨 STOK KRITIS!',
                            html: `<strong>${lowStockItems.length} bahan</strong> hampir habis`,
                            showConfirmButton: false, timer: 5000, timerProgressBar: true, background: '#ff6b6b', color: '#fff'
                        });
                    }
                }
                return json.aaData;
            },
        },
        columns: [
            { data: "id", render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1, className: "text-center align-middle" },
            { name: "name", data: "name", className: "fw-bold align-middle" },
            { name: "category", data: "category", className: "align-middle" },
            { name: "stock", data: "stock", className: "text-end pe-4 fw-bold align-middle" },
            { name: "unit", data: "unit", className: "align-middle" },
            { name: "stock", data: "stock", render: function (data) {
                let stok = parseFloat(data);
                if (stok === 0) return '<span class="badge fs-6" style="background-color: #F2C2B8; color: #50200C !important;">HABIS</span>';
                if (stok < 5) return '<span class="badge fs-6" style="background-color: #FAE8A4; color: #50200C !important;">KRITIS!</span>';
                if (stok < 20) return '<span class="badge" style="background-color: #F7B267; color: #50200C;">Menipis</span>';
                return '<span class="badge" style="background-color: #A8D5BA; color: #50200C;">Tersedia</span>';
            }, className: "align-middle" },
            { name: "description", data: "description", className: "align-middle" },
            { data: "id", className: "text-center align-middle", render: function (id) {
                return `<button class="btn btn-sm btn-light border text-primary shadow-sm me-1" data-action="edit" data-id="${id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-light border text-danger shadow-sm delete-btn" data-id="${id}" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        <form id="delete-form-${id}" action="/ingredient/${id}" method="POST" style="display:none;"><input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr("content")}"><input type="hidden" name="_method" value="DELETE"></form>`;
            }}
        ],
        createdRow: function (row, data) {
            if (data.is_low_stock) { $(row).addClass('low-stock-row'); $(row).find('td:eq(1)').prepend('<i class="fas fa-exclamation-triangle me-2 low-stock-icon"></i>'); }
        },
        drawCallback: function() { $('.low-stock-row').each(function() { $(this).css('background-color', '#ff6b6b'); }); }
    });

    $("#category_filter").on("change", function () { hasPlayedWarningSound = false; datatable.ajax.reload(); });

    // ================================================================
    // 4. LOGIKA TOMBOL & FORM (DIPERBAIKI SECARA FUNDAMENTAL)
    // ================================================================

    // [NEW] Global Click Listener untuk Tombol Simpan
    // Menggunakan $(document).on menjamin tombol tetap bisa diklik walaupun elemen lain berubah
    $(document).on("click", "#btn-modal-save", function(e) {
        e.preventDefault();
        console.log("💾 Tombol Simpan diklik! Mencari form...");

        // Cari form apapun yang ada di dalam modal body
        // Ini lebih aman daripada mencari ID spesifik yang mungkin salah ketik
        const form = $("#ingredient-modal .modal-body form");

        if (form.length > 0) {
            console.log("📝 Form ditemukan:", form.attr('id'));
            // Trigger submit event secara manual pada form tersebut
            form.trigger('submit'); 
        } else {
            console.error("❌ Form tidak ditemukan dalam modal!");
            // Jika form belum loading, jangan lakukan apa-apa
        }
    });

    // Handler Tambah Bahan Baku
    $("#add-button").on("click", async function () {
        stockOpnameModal.hide(); laporanModal.hide();
        
        // Reset tombol simpan sebelum buka
        $("#btn-modal-save").text("Simpan").attr("disabled", true); 
        
        $("#ingredient-modal .modal-title").text("Tambah Bahan Baku");
        $("#ingredient-modal .modal-body").html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
        
        setTimeout(() => { ingredientModal.show(); }, 400);
        
        try {
            const response = await $.get("/ingredient/create");
            $("#ingredient-modal .modal-body").html(response.view);
        } catch (e) {
            $("#ingredient-modal .modal-body").html(
                '<div class="text-danger">Gagal memuat form.</div>'
            );
        }
        
        // Aktifkan tombol simpan setelah konten dimuat
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
    });

    // Handler Edit Bahan Baku
    $(document).on("click", '[data-action="edit"]', async function () {
        stockOpnameModal.hide(); laporanModal.hide();
        
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        const id = $(this).data("id");
        
        $("#ingredient-modal .modal-title").text("Edit Bahan Baku");
        $("#ingredient-modal .modal-body").html('<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>');
        
        setTimeout(() => { ingredientModal.show(); }, 400);
        
        try {
            const response = await $.get(`/ingredient/${id}/edit`);
            $("#ingredient-modal .modal-body").html(response.view);
        } catch (e) {
            $("#ingredient-modal .modal-body").html(
                '<div class="text-danger">Gagal memuat data.</div>'
            );
        }
        
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
    });

    // Handler Form Submit (Menangani semua form simpan bahan baku)
    $(document).on("submit", "#form-save-ingredient", async function (e) {
        e.preventDefault();
        console.log("🚀 Form sedang disubmit...");

        $(".is-invalid").removeClass("is-invalid"); 
        $(".error").text("");
        
        let btnSave = $("#btn-modal-save");
        let originalText = btnSave.text();
        btnSave
            .attr("disabled", true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        try {
            const response = await $.ajax({
                url: $(this).attr("action"), method: $(this).attr("method"), data: $(this).serialize()
            });
            
            ingredientModal.hide();
            Swal.fire({
                icon: "success", title: "Berhasil!", text: response.message,
                timer: 1500, showConfirmButton: false,
                iconColor: '#50200C', customClass: { title: 'swal-title-brown' }
            });
            hasPlayedWarningSound = false; datatable.ajax.reload();
        } catch (error) {
            console.error("Submit Error:", error);
            if (error.status === 422) {
                const errors = error.responseJSON.errors;
                for (const [field, messages] of Object.entries(errors)) {
                    $(`#error_${field}`).text(messages[0]); $(`[name="${field}"]`).addClass("is-invalid");
                }
            } else {
                Swal.fire({ icon: "error", title: "Error", text: "Terjadi kesalahan!", iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
            }
        } finally {
            btnSave.attr("disabled", false).text(originalText);
        }
    });

    // Handler Delete
    $(document).on("click", ".delete-btn", function () {
        const id = $(this).data("id");
        Swal.fire({
            title: "Hapus bahan ini?", 
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
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await $.ajax({ url: $(`#delete-form-${id}`).attr("action"), method: "POST", data: $(`#delete-form-${id}`).serialize() });
                    Swal.fire({ title: "Terhapus!", text: "Data berhasil dihapus.", icon: "success", iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
                    datatable.ajax.reload();
                } catch (e) {
                    Swal.fire({ title: "Gagal", text: "Gagal menghapus data.", icon: "error", iconColor: '#50200C', customClass: { title: 'swal-title-brown' } });
                }
            }
        });
    });
});

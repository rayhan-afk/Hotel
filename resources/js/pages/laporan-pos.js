$(function () {
    const currentRoute = window.location.pathname;
    // Cek route agar script tidak jalan di halaman lain
    if (!currentRoute.includes("laporan/pos")) return;

    console.log("Laporan Pos JS Loaded");

    const tableSelector = "#tableLaporanPos";
    // Ambil URL dari atribut data-route di HTML
    const routeUrl = $(tableSelector).data("route");

    const datatable = $(tableSelector).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: routeUrl,
            type: "GET",
            data: function (d) {
                // Mengirim parameter filter tanggal ke Controller
                d.start_date = $("#startDate").val();
                d.end_date = $("#endDate").val();
            },
            error: function (xhr, error, thrown) {
                console.error("DataTables Error:", xhr.responseText);
                alert(
                    "Terjadi kesalahan saat memuat data. Cek Console (F12) untuk detail."
                );
            },
        },
        columns: [
            // 0. No
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
                className: "text-center align-middle ps-3",
            },
            // 1. Invoice
            {
                data: "invoice_number",
                name: "invoice_number",
                className: "fw-bold text-dark align-middle",
                render: function (data) {
                    return `<span style="color: #50200C;">${data}</span>`;
                },
            },
            // 2. Waktu
            {
                data: "created_at",
                name: "created_at",
                className: "align-middle",
            },
            // 3. Menu Terjual
            {
                data: "items",
                name: "items",
                orderable: false,
                className: "align-middle",
            },
            // 4. Metode Pembayaran
            {
                data: "payment_method",
                name: "payment_method",
                className: "text-center align-middle",
                render: function (data) {
                    if (!data) return "-";
                    let method = data.charAt(0).toUpperCase() + data.slice(1);

                    const styleGreen =
                        "background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;";
                    const styleBlue =
                        "background-color: #B8D8F2; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;";

                    if (method === "Cash" || method === "Tunai") {
                        return `<span class="badge rounded-pill" style="${styleGreen}">${method}</span>`;
                    }
                    return `<span class="badge rounded-pill" style="${styleBlue}">${method}</span>`;
                },
            },
            // 5. Total Amount (DIPERBAIKI AGAR TIDAK Rp 0 / RpNaN)
            {
                data: "total_amount", // <--- Pastikan nama ini sama dengan di Controller (misal: grand_total)
                name: "total_amount",
                className: "text-end fw-bold align-middle pe-3",
                render: function (data, type, row) {
                    // --- DEBUG LOG ---
                    // Jika di browser console (F12) muncul "undefined", berarti nama kolom salah
                    // console.log("Raw Data Row:", row);

                    // 1. Validasi Data Kosong
                    if (data === null || data === undefined || data === "") {
                        return "Rp 0";
                    }

                    // 2. Konversi ke Angka (Handling String vs Number)
                    let amount = parseFloat(data);

                    // 3. Cek apakah hasil konversi Valid
                    if (isNaN(amount)) {
                        return "Rp 0";
                    }

                    // 4. Format Rupiah
                    return new Intl.NumberFormat("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    }).format(amount);
                },
            },
        ],
        order: [[2, "desc"]],
        language: {
            emptyTable: `<div class="d-flex flex-column align-items-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Belum ada data laporan</p>
                        </div>`,
            zeroRecords: `<div class="d-flex flex-column align-items-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Belum ada data laporan</p>
                        </div>`
        },
        // Footer Callback (Hitung Total Halaman Ini)
        footerCallback: function (row, data, start, end, display) {
            let api = this.api();

            // Helper: Hapus semua karakter kecuali angka dan minus, lalu parse float
            let intVal = function (i) {
                if (typeof i === "string") {
                    // Hapus Rp, titik ribuan, spasi, dll.
                    // Jika format '15.000,00', ganti koma jadi titik dulu
                    let clean = i.replace(/[\Rp\.]/g, "").replace(",", ".");
                    return parseFloat(clean) || 0;
                }
                return typeof i === "number" ? i : 0;
            };

            // Ambil data dari kolom index 5 (Total Amount)
            let pageTotal = api
                .column(5, { page: "current" })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            // Tampilkan di Footer
            $("#pageTotal").html(
                new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                }).format(pageTotal)
            );
        },
    });

    // --- EVENT LISTENERS ---

    // Tombol Cari
    $("#btnFilter").on("click", function (e) {
        e.preventDefault();
        datatable.ajax.reload();
    });

    // Tombol Reset
    $("#btnReset").on("click", function (e) {
        e.preventDefault();
        // Reset tanggal ke hari ini
        const today = new Date().toISOString().split("T")[0];
        $("#startDate").val(today);
        $("#endDate").val(today);
        datatable.ajax.reload();
    });

    // Tombol Export Excel
    $("#btnExport").on("click", function (e) {
        e.preventDefault();
        const tglMulai = $("#startDate").val();
        const tglSelesai = $("#endDate").val();

        let baseUrl = $(this).data("route-export");

        // Buat URL Query String
        let url = `${baseUrl}?`;
        if (tglMulai) url += `start_date=${tglMulai}&`;
        if (tglSelesai) url += `end_date=${tglSelesai}`;

        window.location.href = url;
    });
});

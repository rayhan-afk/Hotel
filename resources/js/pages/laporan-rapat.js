$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("laporan/rapat")) return;

    console.log("Laporan Rapat JS Loaded");

    const datatable = $("#laporan-rapat-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/laporan/rapat",
            type: "GET",
            data: function (d) {
                d.tanggal_mulai = $("#tanggal_mulai").val();
                d.tanggal_selesai = $("#tanggal_selesai").val();
            },
            error: function (xhr, error, thrown) {
                console.error("DataTables Error:", xhr.responseText);
            }
        },
        columns: [
            // 0. Instansi
            { 
                data: "instansi", 
                name: "rapat_customers.instansi", 
                className: "fw-bold text-dark align-middle" 
            },
            // 1. Tanggal
            { 
                data: "tanggal", 
                name: "rapat_transactions.tanggal_pemakaian", 
                className: "align-middle" 
            },
            // 2. Waktu
            { 
                data: "waktu", 
                name: "rapat_transactions.waktu_mulai", 
                orderable: false, 
                className: "align-middle" 
            },
            // 3. Paket
            { 
                data: "paket", 
                name: "ruang_rapat_pakets.name", 
                className: "align-middle" 
            },
            // 4. Jumlah Peserta
            { 
                data: "jumlah_peserta", 
                name: "rapat_transactions.jumlah_peserta", 
                className: "text-center align-middle" 
            },
            // 5. Total Pembayaran
            { 
                data: "total_pembayaran", 
                name: "rapat_transactions.total_pembayaran", 
                className: "text-end fw-bold align-middle",
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data);
                }
            },
            // 6. Status (Style Pastel)
            { 
                data: "status", 
                name: "rapat_transactions.status_pembayaran",
                className: "text-center align-middle",
                render: function(data) {
                    // Style Hijau
                    const styleGreen = 'background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
                    // Style Merah
                    const styleRed = 'background-color: #F2C2B8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';

                    if (data === 'Paid' || data === 'Lunas') {
                        return `<span class="badge rounded-pill" style="${styleGreen}">Lunas</span>`;
                    }
                    return `<span class="badge rounded-pill" style="${styleRed}">${data}</span>`;
                }
            },
            // 7. [BARU] Aksi (Invoice)
            { 
                data: "aksi", 
                name: "aksi", 
                orderable: false, 
                searchable: false, 
                className: "text-center align-middle",
            }
        ],
        order: [[1, 'desc']], 
        language: {
            emptyTable: `<div class="d-flex flex-column align-items-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Belum ada data laporan</p>
                        </div>`
        }
    });

    // Event Listeners
    $("#btn-filter").on("click", function (e) {
        e.preventDefault();
        datatable.ajax.reload(); 
    });

    $("#btn-reset").on("click", function (e) {
        e.preventDefault();
        $("#tanggal_mulai").val('');
        $("#tanggal_selesai").val('');
        datatable.ajax.reload(); 
    });

    // Export
    $("#btn-export").on("click", function (e) {
        e.preventDefault();
        const tglMulai = $("#tanggal_mulai").val();
        const tglSelesai = $("#tanggal_selesai").val();
        let url = "/laporan/rapat/export?";
        if(tglMulai) url += `tanggal_mulai=${tglMulai}&`;
        if(tglSelesai) url += `tanggal_selesai=${tglSelesai}`;
        window.location.href = url;
    });
});
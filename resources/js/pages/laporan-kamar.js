$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("laporan/kamar")) return;

    console.log("Laporan Kamar JS Loaded");

    const datatable = $("#laporan-kamar-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/laporan/kamar",
            type: "GET",
            data: function (d) {
                d.start_date = $("#start_date").val();
                d.end_date = $("#end_date").val();
            },
            error: function (xhr, error, thrown) {
                console.error("DataTables Error:", xhr.responseText);
            }
        },
        columns: [
            // 0. Tamu
            { 
                data: "tamu", 
                name: "customers.name", 
                className: "fw-bold text-dark align-middle" 
            },
            // 1. Kamar
            { 
                data: "kamar", 
                name: "rooms.number", 
                className: "align-middle" 
            },
            // 2. Check-In
            { 
                data: "check_in", 
                name: "transactions.check_in", 
                className: "align-middle" 
            },
            // 3. Check-Out
            { 
                data: "check_out", 
                name: "transactions.check_out", 
                className: "align-middle" 
            },
            // 4. Sarapan
            { 
                data: "sarapan", 
                name: "transactions.breakfast", 
                className: "text-center align-middle",
                render: function(data) {
                    if (data === 'Yes' || data === 1 || data === '1') {
                        return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C;
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">
                                            <i class="fas fa-utensils me-1" style="color: #50200C; font-size: 10px;"></i>Ya
                                    </span>`;
                    }
                    return `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; 
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">Tidak</span>`;
                }
            },
            // 5. Total Harga
            { 
                data: "total_harga", 
                name: "transactions.total_price", 
                className: "text-end fw-bold align-middle",
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data);
                }
            },
            // 6. Status (INI SAJA YANG DIUBAH)
            { 
                data: "status", 
                name: "transactions.status", 
                className: "text-center align-middle",
                render: function(data) {
                    // Style Hijau (Sama dengan Sarapan 'Ya')
                    const styleGreen = 'background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
                    // Style Merah (Sama dengan Sarapan 'Tidak')
                    const styleRed = 'background-color: #F2C2B8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';

                    // Bersihkan HTML tag dari data mentah jika ada
                    let text = data ? data.replace(/<[^>]*>?/gm, '') : ''; 

                    if (text === 'Selesai' || text === 'Lunas' || text === 'Done' || text === 'Paid') {
                        return `<span class="badge rounded-pill" style="${styleGreen}">
                                    ${text}
                                </span>`;
                    }
                    
                    // Default / Cancelled
                    return `<span class="badge rounded-pill" style="${styleRed}">
                                ${text}
                            </span>`;
                }
            },
            // 7. Aksi (TIDAK DISENTUH SAMA SEKALI)
            // Render default DataTables akan menampilkan HTML button yang dikirim dari server
            { 
                data: "aksi", 
                name: "aksi", 
                orderable: false, 
                searchable: false, 
                className: "text-center align-middle" 
            }
        ],
        order: [[2, 'desc']], // Urutkan Check-In Terbaru
        language: {
            emptyTable: `<div class="d-flex flex-column align-items-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Belum ada data laporan</p>
                        </div>`
        }
    });

    // --- EVENT LISTENERS ---

    $("#btn-filter").on("click", function (e) {
        e.preventDefault();
        datatable.ajax.reload();
    });

    $("#btn-reset").on("click", function (e) {
        e.preventDefault();
        $("#start_date").val('');
        $("#end_date").val('');
        datatable.ajax.reload();
    });

    $("#btn-export-kamar").on("click", function (e) {
        e.preventDefault();
        const startDate = $("#start_date").val();
        const endDate = $("#end_date").val();
        
        let url = "/laporan/kamar/export?";
        if(startDate) url += `start_date=${startDate}&`;
        if(endDate) url += `end_date=${endDate}`;
        
        window.location.href = url;
    });
});
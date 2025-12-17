$(function () {
    const currentRoute = window.location.pathname;
    // Cek route agar tidak bentrok
    if (!currentRoute.includes("room-info/available")) return;

    const tableElement = $("#available-room-table");
    let datatable = null;

    if (tableElement.length > 0) {
        datatable = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/room-info/available",
                type: "GET",
                data: function (d) {
                    d.type = $("#type").val();
                    // Opsional: Jika nanti butuh filter tanggal check_date, tambahkan di sini
                    // d.check_date = $("#check_date").val(); 
                },
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                // 0. No
                { 
                    name: "number", 
                    data: "number",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                // 1. Nomor Kamar (Asli)
                { name: "number", data: "number" },
                // 2. Nama Kamar
                { name: "name", data: "name" },
                // 3. Tipe
                { name: "types.name", data: "type" },
                // 4. Luas
                { 
                    name: "area_sqm", 
                    data: "area_sqm", 
                    render: function(data) { return data ? data + ' m²' : '-'; } 
                },
                // 5. Fasilitas Kamar
                { 
                    name: "room_facilities", 
                    data: "room_facilities", 
                    render: function(data) { return data ? (data.length > 30 ? data.substr(0, 30) + '...' : data) : '-'; } 
                },
                // 6. Fasilitas Mandi
                { 
                    name: "bathroom_facilities", 
                    data: "bathroom_facilities", 
                    render: function(data) { return data ? (data.length > 30 ? data.substr(0, 30) + '...' : data) : '-'; } 
                },
                // 7. Kapasitas
                { name: "capacity", data: "capacity", className: "text-center" },
                // 8. Harga
                {
                    name: "price",
                    data: "price",
                    className: "text-end",
                    render: function (price) {
                        return `<div class="fw-bold" style="color: #50200C;">Rp ${new Intl.NumberFormat('id-ID').format(price)}</div>`;
                    },
                },
                // 9. Status (LOGIKA BARU - DINAMIS)
                {
                    name: "id", // Tetap 'id' atau kosong karena sorting biasanya disable di sini
                    data: "status", // Ambil data 'status' dari JSON repository
                    className: "text-center",
                    orderable: false,
                    searchable: false,
                    render: function (status, type, row) {
                        // Style Default (Font Size & Padding)
                        const styleBase = 'font-size: 10px; padding: 6px 12px; font-weight: 700;';

                        if (status === 'Menunggu Checkout') {
                            // KUNING (Warning)
                            return `<span class="badge rounded-pill bg-warning text-dark" style="${styleBase}">
                                <i class="fas fa-clock me-1"></i> Menunggu Checkout
                            </span>`;
                        } 
                        else if (status === 'Sedang Dibersihkan') {
                            // BIRU (Info)
                            return `<span class="badge rounded-pill bg-info text-white" style="${styleBase}">
                                <i class="fas fa-broom me-1"></i> Sedang Dibersihkan
                            </span>`;
                        } 
                        else {
                            // HIJAU (Tersedia / Default Style Kamu)
                            return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C; ${styleBase}">
                                <i class="fas fa-check me-1"></i> Tersedia
                            </span>`;
                        }
                    }
                },
            ],
            order: [[1, 'asc']], // Sort by Nomor Kamar
            language: {
                emptyTable: "Tidak ada data kamar tersedia saat ini.",
                processing: "Memuat data...",
                zeroRecords: "Data tidak ditemukan"
            }
        });
    }

    // Filter Type Change
    $("#type").on("change", function () {
        if (datatable) {
            datatable.ajax.reload();
        }
    });
});
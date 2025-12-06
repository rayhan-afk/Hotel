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
                },
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                // No
                { 
                    name: "number", // Sort by number
                    data: "number",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                // Nomor Kamar (Asli)
                { name: "number", data: "number" },
                // Nama Kamar
                { name: "name", data: "name" },
                // Tipe
                { 
                    name: "types.name", // Sort by relasi
                    data: "type" 
                },
                // Luas
                { 
                    name: "area_sqm", 
                    data: "area_sqm", 
                    render: function(data) { return data ? data + ' m²' : '-'; } 
                },
                // Fasilitas Kamar
                { 
                    name: "room_facilities", 
                    data: "room_facilities", 
                    render: function(data) { return data ? (data.length > 30 ? data.substr(0, 30) + '...' : data) : '-'; } 
                },
                // Fasilitas Mandi
                { 
                    name: "bathroom_facilities", 
                    data: "bathroom_facilities", 
                    render: function(data) { return data ? (data.length > 30 ? data.substr(0, 30) + '...' : data) : '-'; } 
                },
                // Kapasitas
                { name: "capacity", data: "capacity", className: "text-center" },
                // Harga
                {
                    name: "price",
                    data: "price",
                    className: "text-end",
                    render: function (price) {
                        return `<div class="fw-bold style="color: #50200C;">Rp ${new Intl.NumberFormat('id-ID').format(price)}</div>`;
                    },
                },
                // Status (Logic Tersedia)
                {
                    name: "id", // Dummy
                    data: "id",
                    className: "text-center",
                    orderable: false,
                    searchable: false,
                    render: function (id) {
                        // Karena halaman ini sudah memfilter yang tersedia, kita hardcode statusnya
                        return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C;
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">Tersedia</span>`;
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
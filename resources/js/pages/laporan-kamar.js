$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("laporan/kamar")) return;

    console.log("Laporan Kamar JS Loaded (Time Logic Added)");

    // --- STYLE DEFINITIONS (Sesuai Style Asli Anda) ---
    const styleGreen = 'background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
    const styleRed = 'background-color: #F2C2B8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
    const styleBlue = 'background-color: #D6EAF8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
    
    // Style Baru untuk Peringatan Waktu (Kuning/Orange Lembut)
    const styleWarning = 'background-color: #FFE6CC; color: #50200C; font-size: 9px; padding: 4px 8px; font-weight: 700;';

    const datatable = $("#laporan-kamar-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
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
            // 0. NO
            {
                data: null,
                className: "text-center align-middle",
                orderable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },

            // 1. TAMU & KAMAR
            {
                data: "customer_name",
                name: "customers.name",
                className: "align-middle",
                render: function (data, type, row) {
                    let roomNum = row.room ? row.room.number : '-';
                    let roomType = row.room ? row.room.type.name : '-';
                    return `<div class="d-flex flex-column">
                                <span class="fw-bold" style="color: #50200C">${data}</span>
                                <small style="color: #50200C;"><i class="fas fa-bed me-1"></i>${roomNum} - ${roomType}</small>
                            </div>`;
                }
            },

            // 2. PAKET MENGINAP (LOGIKANYA DIPERBAIKI)
            {
                data: null,
                className: "align-middle",
                searchable: false,
                render: function (data, type, row) {
                    let pricePerNight = row.room_price ? row.room_price : (row.room ? row.room.price : 1);
                    if(pricePerNight <= 0) pricePerNight = 1;
                    
                    let duration = Math.round(row.total_price / pricePerNight);
                    if (duration < 1) duration = 1;

                    // [UBAH DISINI] Gunakan 'original_date' untuk menghitung paket rencana
                    let planCheckIn = moment(row.check_in); 
                    let planOut = planCheckIn.clone().add(duration, 'days');

                    return `<div class="small" style="color: #50200C;">
                                <div>In: ${planCheckIn.format('DD/MM/YYYY')}</div>
                                <div class="fw-bold">Durasi: ${duration} Malam</div>
                                <div class="border-top mt-1 pt-1" style="color: #A94442">Out: ${planOut.format('DD/MM/YYYY')}</div>
                            </div>`;
                }
            },

            // 3. MASUK REAL (+ Logic Early Check-in < 14:00)
            {
                data: "check_in",
                name: "transactions.check_in",
                className: "align-middle",
                render: function (data) {
                    let date = moment(data);
                    
                    // Logic Early Checkin (Sebelum jam 14)
                    let isEarlyIn = date.hour() < 14; 
                    let badgeEarly = isEarlyIn 
                        ? `<br><span class="badge rounded-pill mt-1" style="${styleWarning}">Early Check-in</span>` 
                        : '';

                    return `<div>
                                ${date.format('DD/MM/YYYY')}<br>
                                <span class="badge rounded-pill" style="${styleGreen}">
                                    <i class="fas fa-clock me-1"></i>${date.format('HH:mm')}
                                </span>
                                ${badgeEarly}
                            </div>`;
                }
            },

            // 4. KELUAR REAL (+ Logic Late Check-out > 12:00)
            {
                data: "check_out",
                name: "transactions.check_out",
                className: "align-middle",
                render: function (data, type, row) {
                    if (row.status === 'Check In' || !data) {
                        return `<span class="badge rounded-pill" style="${styleBlue}">Belum Keluar</span>`;
                    }

                    let actualOut = moment(data);
                    
                    // A. Logic Late Check-out (Lewat jam 12 siang)
                    // Jika jam >= 12, maka Late (misal 12:01 atau 13:00)
                    let isLateOut = actualOut.hour() >= 12;
                    
                    // B. Logic Early Checkout (Pulang sebelum tanggal kontrak selesai)
                    let pricePerNight = row.room_price ? row.room_price : (row.room ? row.room.price : 1);
                    if(pricePerNight <= 0) pricePerNight = 1;
                    let duration = Math.round(row.total_price / pricePerNight);
                    if (duration < 1) duration = 1;
                    let planOutDate = moment(row.check_in).add(duration, 'days').startOf('day');
                    let actualOutDate = moment(data).startOf('day');
                    let isEarlyDate = actualOutDate.isBefore(planOutDate);

                    // Badge Waktu
                    let timeBadge = `<span class="badge rounded-pill" style="${styleRed}">
                                        <i class="fas fa-clock me-1"></i>${actualOut.format('HH:mm')}
                                     </span>`;
                    
                    // Badge Peringatan
                    let alertBadge = '';
                    if (isEarlyDate) {
                        // Pulang beda hari (lebih cepat)
                        alertBadge = `<br><span class="badge rounded-pill mt-1" style="background-color: #50200C; color: #fff; font-size: 9px; padding: 4px 8px;">Pulang Awal (Early)</span>`;
                    } else if (isLateOut) {
                        // Pulang hari yang sama, tapi telat jam
                        alertBadge = `<br><span class="badge rounded-pill mt-1" style="${styleWarning}">Late Check-out</span>`;
                    }

                    return `<div>
                                ${actualOut.format('DD/MM/YYYY')}<br>
                                ${timeBadge}
                                ${alertBadge}
                            </div>`;
                }
            },

            // 5. TOTAL HARGA
            {
                data: "total_price",
                name: "transactions.total_price",
                className: "text-end fw-bold align-middle",
                render: function (data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data);
                }
            },

            // 6. STATUS
            {
                data: "status",
                name: "transactions.status",
                className: "text-center align-middle",
                render: function (data) {
                    let text = data ? data.replace(/<[^>]*>?/gm, '') : '';
                    if (text === 'Done' || text === 'Payment Done' || text === 'Selesai') {
                        return `<span class="badge rounded-pill" style="${styleGreen}">${text}</span>`;
                    }
                    if (text === 'Check In') {
                        return `<span class="badge rounded-pill" style="${styleBlue}">${text}</span>`;
                    }
                    return `<span class="badge rounded-pill" style="${styleRed}">${text}</span>`;
                }
            },

            // 7. AKSI
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center align-middle"
            }
        ],
        order: [[3, 'desc']], 
        language: {
            emptyTable: `<div class="d-flex flex-column align-items-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Belum ada data laporan</p>
                        </div>`
        }
    });

    // --- EVENT LISTENERS ---
    $("#btn-filter").on("click", function (e) { e.preventDefault(); datatable.ajax.reload(); });
    $("#btn-reset").on("click", function (e) { e.preventDefault(); $("#start_date").val(''); $("#end_date").val(''); datatable.ajax.reload(); });
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
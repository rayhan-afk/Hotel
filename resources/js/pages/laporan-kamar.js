$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("laporan/kamar")) return;

    console.log("Laporan Kamar JS Loaded (With Guest Count)");

    // --- STYLE DEFINITIONS (Tetap) ---
    const styleGreen = 'background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
    const styleRed = 'background-color: #F2C2B8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
    const styleBlue = 'background-color: #D6EAF8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;';
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

            // 1. TAMU & KAMAR (DIPERBARUI)
            {
                data: "customer_name",
                name: "customers.name",
                className: "align-middle",
                render: function (data, type, row) {
                    // Info Kamar
                    let roomNum = row.room ? row.room.number : '-';
                    let roomType = row.room ? row.room.type.name : '-';
                    
                    // Info Jumlah Tamu (Data dari Repository)
                    let pCount = row.count_person || 1;
                    let cCount = row.count_child || 0;
                    let guestText = `${pCount} Dewasa`;
                    if(cCount > 0) guestText += `, ${cCount} Anak`;

                    return `<div class="d-flex flex-column">
                                <span class="fw-bold" style="color: #50200C; font-size: 1.05em;">${data}</span>
                                <div class="d-flex align-items-center mt-1">
                                    <small style="color: #50200C;" class="me-2 border-end pe-2">
                                        <i class="fas fa-bed me-1" style="color: #C49A6C"></i>${roomNum} - ${roomType}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1" style="color: #8FB8E1"></i>${guestText}
                                    </small>
                                </div>
                            </div>`;
                }
            },

            // 2. PAKET MENGINAP (RENCANA)
            {
                data: "check_in", 
                name: "transactions.check_in",
                className: "align-middle",
                searchable: false,
                render: function (data, type, row) {
                    let planIn = moment(row.check_in);
                    let planOut = moment(row.check_out);
                    
                    // Hitung durasi rencana
                    let duration = planOut.diff(planIn, 'days');
                    if (duration < 1) duration = 1; 

                    return `<div class="small" style="color: #50200C;">
                                <div>In: ${planIn.format('DD/MM/YYYY')}</div>
                                <div class="fw-bold my-1" style="font-size: 0.85rem;">Durasi: ${duration} Malam</div>
                                <div class="text-muted">Out: ${planOut.format('DD/MM/YYYY')}</div>
                            </div>`;
                }
            },

            // 3. MASUK REAL
            {
                data: "check_in",
                name: "transactions.check_in",
                className: "align-middle",
                render: function (data) {
                    let date = moment(data);
                    
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

            // 4. KELUAR REAL
            {
                data: "updated_at",
                name: "transactions.updated_at",
                className: "align-middle",
                render: function (data, type, row) {
                    if (row.status === 'Check In' || row.status === 'Reservation') {
                        return `<span class="badge rounded-pill" style="${styleBlue}">Belum Keluar</span>`;
                    }

                    // Gunakan updated_at sebagai waktu keluar real (saat status berubah jadi Done)
                    let actualOut = moment(data); 
                    let planOut = moment(row.check_out);

                    // Logic Telat Checkout (Lebih dari jam 12 siang)
                    let isLateOut = actualOut.hour() >= 12 && actualOut.minute() > 30; // Toleransi sampai 12:30
                    
                    let actualDateOnly = actualOut.clone().startOf('day');
                    let planDateOnly = planOut.clone().startOf('day');
                    let isEarlyDate = actualDateOnly.isBefore(planDateOnly);

                    let timeBadge = `<span class="badge rounded-pill" style="${styleRed}">
                                            <i class="fas fa-clock me-1"></i>${actualOut.format('HH:mm')}
                                     </span>`;
                    
                    let alertBadge = '';
                    if (isEarlyDate) {
                        alertBadge = `<br><span class="badge rounded-pill mt-1" style="background-color: #E67E22; color: #fff; font-size: 9px; padding: 4px 8px;">Early Check-out</span>`;
                    } else if (isLateOut) {
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
                    if (text === 'Done' || text === 'Payment Done' || text === 'Selesai' || text === 'Cleaning') {
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
    
    // EXPORT EXCEL
    $("#btn-export-kamar").on("click", function (e) {
        e.preventDefault();
        const startDate = $("#start_date").val();
        const endDate = $("#end_date").val();
        let url = "/laporan/kamar/export?";
        if(startDate) url += `start_date=${startDate}&`;
        if(endDate) url += `end_date=${endDate}`;
        window.location.href = url;
    });

    // EXPORT PDF
    $("#btn-export-pdf").on("click", function (e) {
        e.preventDefault();
        
        // 1. Ambil Tanggal Filter
        const startDate = $("#start_date").val();
        const endDate = $("#end_date").val();
        
        // 2. Susun URL
        let url = "/laporan/kamar/pdf?";
        if(startDate) url += `start_date=${startDate}&`;
        if(endDate) url += `end_date=${endDate}`;
        
        // 3. Buka di Tab Baru
        window.open(url, '_blank');
    });
});
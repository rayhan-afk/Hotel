$(function () {
    const currentRoute = window.location.pathname;
    // Pastikan script hanya jalan di halaman reservasi
    if (!currentRoute.includes("room-info/reservation")) return;

    const tableElement = $("#reservation-table");

    if (tableElement.length > 0) {
        const table = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/room-info/reservation",
                type: "GET",
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                // 1. No
                { 
                    data: null, 
                    sortable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                // 2. Tamu
                { 
                    name: "customers.name", 
                    data: "customer_name",
                    className: "fw-bold text-primary"
                },
                // 3. Kamar
                { 
                    name: "rooms.number", 
                    data: "room_info",
                    render: function(data) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-bold">${data.number}</span>
                                <span class="small">${data.type}</span>
                            </div>
                        `;
                    }
                },
                // 4. Check In
                { name: "transactions.check_in", data: "check_in" },
                // 5. Check Out
                { name: "transactions.check_out", data: "check_out" },
                // 6. Sarapan
                { 
                    name: "transactions.breakfast",
                    data: "breakfast",
                    className: "text-center",
                    orderable: false,
                    render: function(data) {
                        return data == 'Yes' 
                            ? `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C;
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">
                                        <i class="fas fa-utensils me-1" style="color: #50200C; font-size: 10px;"></i>Ya
                                    </span>`
                            : `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; 
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">Tidak</span>`;
                    }
                },
                // 7. Total Harga
                { 
                    name: "rooms.price", 
                    data: "total_price",
                    className: "text-end fw-bold",
                    render: function(data) {
                        return new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR',
                            minimumFractionDigits: 0 
                        }).format(data);
                    }
                },
                // 8. Status
                { 
                    name: "transactions.status", 
                    data: "status",
                    className: "text-center",
                    render: function(data) {
                        // Badge Kuning Khas Reservasi
                        return `<span class="badge rounded-pill" style="background-color: #FAE8A4; color: #50200C;
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">${data}</span>`;
                    }
                },
                // 9. Aksi (Check In & Cancel)
                {
                    data: 'raw_id', // ID Transaksi
                    orderable: false,
                    searchable: false,
                    className: "text-center align-middle",
                    width: "15%",
                    render: function(id) {
                        return `
                            <div class="d-flex justify-content-center gap-2">

                                <button class="btn btn-sm btn-success btn-checkin shadow-sm fw-bold px-3" 
                                        data-id="${id}" style="color: #50200C;"
                                        data-bs-toggle="tooltip" 
                                        title="Proses Check In">
                                    <i class="fas fa-door-open me-2"></i> Check In
                                </button>

                                <button class="btn btn-sm btn-danger btn-cancel shadow-sm fw-bold px-3" 
                                        data-id="${id}" style="color: #50200C;"
                                        data-bs-toggle="tooltip" 
                                        title="Batalkan Reservasi">
                                    <i class="fas fa-ban me-2"></i> Cancel
                                </button>

                            </div>
                        `;
                    }
                }
            ],
            order: [[3, 'asc']], // Urutkan berdasarkan Check-in terdekat
            drawCallback: function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            },
            language: {
                emptyTable: "Tidak ada data reservasi yang tersedia saat ini.",
                processing: "Memuat data...",
                zeroRecords: "Data tidak ditemukan"
            }
        });

        // === EVENT 1: PROSES CHECK IN (BARU) ===
        $(document).on('click', '.btn-checkin', function() {
            let transactionId = $(this).data('id');
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            Swal.fire({
                html: `<h2 style="color: #50200C; font-weight: bold; margin-top: -10px;">Check In Tamu?</h2>
                <p style="color: #50200C; font-size: 14px; margin-top: 5px;">
                Tamu ini akan diubah statusnya menjadi 'Check In' dan masuk ke daftar tamu aktif.</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: "#F2C2B8",
                cancelButtonColor: "#8FB8E1",
                confirmButtonText: '<i class="fas fa-check me-1"></i> Ya, Check In!',
                cancelButtonText: 'Batal',
                customClass: {
                    title: "swal-title-custom",
                    confirmButton: "text-50200C",
                    cancelButton: "text-50200C",
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Loading State
                    Swal.fire({ title: 'Memproses...', didOpen: () => { Swal.showLoading(); },
                    customClass: {title: 'swal-title-process'} });

                    $.ajax({
                        url: `/room-info/reservation/${transactionId}/check-in`, // Rute baru
                        type: 'POST',
                        data: { _token: csrfToken },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Tamu berhasil Check In.',
                                customClass: {
                                    title: 'swal-title-brown',
                                    htmlContainer: 'swal-text-brown',
                                    confirmButton: 'swal-btn-blue',
                                    icon: 'swal-icon-custom'
                                }
                            });
                            table.ajax.reload(null, false); // Reload tabel
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                            Swal.fire('Gagal!', msg, 'error');
                        }
                    });
                }
            });
        });

        // === EVENT 2: CANCEL RESERVASI (LAMA) ===
        $(document).on('click', '.btn-cancel', function() {
            let transactionId = $(this).data('id');
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', didOpen: () => { Swal.showLoading(); } });

                    $.ajax({
                        url: `/room-info/reservation/${transactionId}/cancel`,
                        type: 'POST',
                        data: { _token: csrfToken },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Tamu berhasil Check In.',
                                customClass: {
                                    title: 'swal-title-brown',
                                    htmlContainer: 'swal-text-brown',
                                    confirmButton: 'swal-btn-blue',
                                    icon: 'swal-icon-custom'
                                }
                            });
                            table.ajax.reload(null, false); 
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        });
    }
});
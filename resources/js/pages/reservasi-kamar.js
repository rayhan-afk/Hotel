$(function () {
    const currentRoute = window.location.pathname;
    // Pastikan script ini hanya jalan di halaman reservasi
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
                { 
                    data: null, 
                    sortable: false,
                    searchable: false,
                    className: "text-center",
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    name: "customers.name", 
                    data: "customer_name",
                    className: "fw-bold text-primary",
                    defaultContent: "Tamu" 
                },
                
                // KOLOM JUMLAH TAMU (Safety Check)
                { 
                    name: "transactions.count_person", 
                    data: "count_person",
                    className: "text-center",
                    searchable: false,
                    render: function(data, type, row) {
                        let personCount = (data !== null && data !== undefined) ? data : 1;
                        let childCount  = (row.count_child !== null && row.count_child !== undefined) ? row.count_child : 0;

                        let text = `<span class="fw-bold">${personCount}</span> Dewasa`;
                        
                        if (childCount > 0) {
                            text += `, <span class="fw-bold">${childCount}</span> Anak`;
                        }
                        
                        return `<span class="badge bg-light text-dark border shadow-sm" style="font-weight: 500;">${text}</span>`;
                    }
                },

                { 
                    name: "rooms.number", 
                    data: "room_info",
                    render: function(data) {
                        if (!data) return '<span class="text-muted">-</span>';
                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-bold">${data.number}</span>
                                <span class="small text-muted">${data.type}</span>
                            </div>
                        `;
                    }
                },
                { name: "transactions.check_in", data: "check_in" },
                { name: "transactions.check_out", data: "check_out" },
                { 
                    name: "transactions.breakfast",
                    data: "breakfast",
                    className: "text-center",
                    orderable: false,
                    render: function(data) {
                        return data == 'Yes' 
                            ? `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;"><i class="fas fa-utensils me-1"></i>Ya</span>`
                            : `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;">Tidak</span>`;
                    }
                },
                { 
                    name: "rooms.price", 
                    data: "total_price",
                    className: "text-end fw-bold",
                    render: function(data) {
                        let price = data ? data : 0;
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
                    }
                },
                { 
                    name: "transactions.status", 
                    data: "status",
                    className: "text-center",
                    render: function(data) {
                        let color = '#FAE8A4'; 
                        if(data === 'Check In') color = '#A8D5BA';
                        if(data === 'Canceled') color = '#F2C2B8';
                        
                        return `<span class="badge rounded-pill" style="background-color: ${color}; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;">${data}</span>`;
                    }
                },
                {
                    data: 'raw_id',
                    orderable: false,
                    searchable: false,
                    className: "text-center align-middle",
                    width: "15%",
                    render: function(id) {
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-success btn-checkin shadow-sm fw-bold px-3" 
                                        data-id="${id}" style="color: #50200C;"
                                        data-bs-toggle="tooltip" title="Proses Check In">
                                    <i class="fas fa-door-open me-2"></i> Check In
                                </button>
                                <button class="btn btn-sm btn-danger btn-cancel shadow-sm fw-bold px-3" 
                                        data-id="${id}" style="color: #50200C;"
                                        data-bs-toggle="tooltip" title="Batalkan Reservasi">
                                    <i class="fas fa-ban me-2"></i> Cancel
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[4, 'asc']], 
            drawCallback: function() {
                if (typeof bootstrap !== 'undefined') {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            },
            // [PERBAIKAN] Menghapus custom text "Maju/Mundur" agar kembali default
            language: {
                emptyTable: "Tidak ada data reservasi yang tersedia saat ini.",
                processing: "Memuat data...",
                zeroRecords: "Data tidak ditemukan",
                search: "Cari Tamu/Kamar:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                // paginate: dihapus agar kembali ke default icon/standard
            }
        });

        // Event Handler CheckIn & Cancel tetap sama
        $(document).on('click', '.btn-checkin', function() {
            let transactionId = $(this).data('id');
            let csrfToken = $('meta[name="csrf-token"]').attr('content'); 

            Swal.fire({
                html: `<h2 style="color: #50200C; font-weight: bold; margin-top: -10px;">Check In Tamu?</h2>
                <p style="color: #50200C; font-size: 14px; margin-top: 5px;">
                Stok Amenities (Sandal, dll) akan otomatis berkurang dari gudang.</p>`,
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
                    Swal.fire({ title: 'Memproses Stok & Check In...', didOpen: () => { Swal.showLoading(); }, customClass: {title: 'swal-title-process'} });

                    $.ajax({
                        url: `/transaction/check-in/${transactionId}/process`, 
                        type: 'POST',
                        data: { _token: csrfToken },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success', title: 'Berhasil!', text: response.message,
                                customClass: { title: 'swal-title-brown', htmlContainer: 'swal-text-brown', confirmButton: 'swal-btn-blue', icon: 'swal-icon-custom' }
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire({ icon: 'error', title: 'Gagal Check In!', text: errorMessage, confirmButtonColor: '#d33', confirmButtonText: 'Tutup' });
                        }
                    });
                }
            });
        });

        $(document).on('click', '.btn-cancel', function() {
            let transactionId = $(this).data('id');
            let url = `/room-info/reservation/${transactionId}/cancel`;
            
            $('#cancelReservationForm').attr('action', url);
            $('#cancelReservationForm')[0].reset();

            if (typeof bootstrap !== 'undefined') {
                let modal = new bootstrap.Modal(document.getElementById('cancelReservationModal'));
                modal.show();
            } else {
                alert("Bootstrap JS not loaded.");
            }
        });

        $('#cancelReservationForm').on('submit', function(e) {
            e.preventDefault(); 
            let form = $(this);
            let url = form.attr('action');
            let formData = form.serialize(); 

            if (typeof bootstrap !== 'undefined') {
                let modalElement = document.getElementById('cancelReservationModal');
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }

            Swal.fire({ title: 'Membatalkan Reservasi...', didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Dibatalkan!',
                        text: 'Reservasi telah dibatalkan.',
                        customClass: { title: 'swal-title-brown', htmlContainer: 'swal-text-brown', confirmButton: 'swal-btn-blue', icon: 'swal-icon-custom' }
                    });
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat membatalkan.', 'error');
                }
            });
        });
    }
});
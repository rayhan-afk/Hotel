$(function () {
    const currentRoute = window.location.pathname;
    // Cek route agar script ini hanya jalan di halaman check-in
    if (!currentRoute.includes("transaction/check-in")) return;

    const tableElement = $("#checkin-table");
    let checkoutId = null; 

    if (tableElement.length > 0) {
        const table = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/transaction/check-in",
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
                                <span class="fw-bold" style="color: #50200C">${data.number}</span>
                                <span class="small" style="color: #50200C">${data.type}</span>
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
                        if (data === 'Yes') {
                            return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C;
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">
                                        <i class="fas fa-utensils me-1" style="color: #50200C; font-size: 10px;"></i>Ya
                                    </span>`;
                        } else {
                            return `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; 
                            font-size: 10px; padding: 6px 12px; font-weight: 700;">Tidak</span>`;
                        }
                    }
                },

                // 7. Total Harga
                { 
                    name: "rooms.price", // Gunakan nama kolom asli untuk sorting
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

                // 8. [BARU] Sisa Bayar (Kekurangan akibat Extend)
                { 
                    name: "transactions.paid_amount", 
                    data: "remaining_payment",
                    className: "text-center", // Diubah jadi center agar badge rapi
                    render: function(data) {
                        let formatted = new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR',
                            minimumFractionDigits: 0 
                        }).format(data);

                        // Jika ada sisa bayar > 0 (MERAH PASTEL - Solid)
                        if (data > 0) {
                            return `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; 
                                    font-size: 13px; padding: 6px 12px; font-weight: 800; border: 1px solid #E57373;">
                                    ${formatted}
                                    </span>`;
                        }
                        
                        // Jika 0 (HIJAU PASTEL - Solid)
                        return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C; 
                                font-size: 11px; padding: 6px 12px; font-weight: 800; border: 1px solid #81C784;">
                                Lunas
                                </span>`;
                    }
                },

                // 9. Status
                { 
                    name: "transactions.status", 
                    data: "status",
                    className: "text-center",
                    render: function(data) {
                        return `<span class="badge px-3 py-1 rounded-pill" style="background-color: #8FB8E1;
                        color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;">${data}</span>`;
                    }
                },
                // 10. Aksi (Edit & Check Out)
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: "text-center align-middle",
                    render: function(id, type, row) {
                        let customerName = row.customer_name ? row.customer_name.replace(/"/g, '&quot;') : '-'; 
                        let roomNumber = row.room_info.number;

                        return `
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <button class="btn btn-light border text-secondary btn-edit shadow-sm" 
                                        data-id="${id}" 
                                        data-bs-toggle="tooltip" 
                                        title="Edit Data / Perpanjang Durasi">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn btn-danger btn-checkout shadow px-3 fw-bold" 
                                        style="min-width: 130px; letter-spacing: 0.5px;" 
                                        data-id="${id}"
                                        data-name="${customerName}"
                                        data-room="${roomNumber}">
                                    <i class="fas fa-sign-out-alt me-2"></i>CHECK OUT
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[3, 'desc']], 
            drawCallback: function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            },
            language: {
                emptyTable: "Tidak ada data tamu yang check in saat ini.",
                processing: "Memuat data...",
                zeroRecords: "Data tidak ditemukan"
            }
        });

        // Event: Tombol Edit (Tampilkan Modal)
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let modal = new bootstrap.Modal(document.getElementById('editCheckinModal'));
            
            $('#editCheckinBody').html('<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
            modal.show();

            $.get(`/transaction/check-in/${id}/edit`, function(response) {
                $('#editCheckinBody').html(response);
            }).fail(function() {
                $('#editCheckinBody').html('<div class="alert alert-danger">Gagal memuat data.</div>');
            });
        });

        // === EVENT: SUBMIT FORM EDIT (UPDATE DURASI/KAMAR) ===
        $(document).on('submit', '#form-edit-checkin', function(e) {
            e.preventDefault(); 

            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize(); 
            let btn = form.find('button[type="submit"]');
            let originalContent = btn.html();

            // Loading State
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST', 
                data: data,
                success: function(response) {
                    // Tutup modal
                    let modalEl = document.getElementById('editCheckinModal');
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diperbarui!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            iconColor: '#50200C', // ✅ Warna icon success
                            customClass: {
                                title: 'swal-title-brown' // ✅ Custom warna title
                            }
                        });
                    } else {
                        alert("SUKSES: " + response.message); 
                    }
                    
                    // Reload tabel agar sisa bayar terupdate
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalContent);
                    
                    let msg = "Gagal menyimpan perubahan.";
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg += "\n" + xhr.responseJSON.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: msg
                        });
                    } else {
                         alert(msg);
                    }
                }
            });
        });

        // Event: Tombol Check Out (Tampilkan Modal Konfirmasi)
        $(document).on('click', '.btn-checkout', function() {
            checkoutId = $(this).data('id');
            let name = $(this).data('name');
            let room = $(this).data('room');

            $('#checkoutCustomerName').text(name);
            $('#checkoutRoomNumber').text(room);

            let modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        });

        // Event: Konfirmasi Check Out di Modal
        $('#btn-confirm-checkout').on('click', function() {
            if(!checkoutId) return;

            let btn = $(this);
            let originalContent = btn.html();
            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memproses...');

            $.ajax({
                url: `/transaction/check-in/${checkoutId}/checkout`,
                type: 'POST',
                data: { _token: csrfToken },
                success: function(response) {
                    let modalEl = document.getElementById('checkoutModal');
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    
                    if(response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        table.ajax.reload();
                        btn.prop('disabled', false).html(originalContent);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalContent);
                    alert("Gagal: " + (xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan sistem"));
                }
            });
        });
    }
});
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
                
                // 6. Extra Bed
                { 
                    data: "extra_bed", 
                    name: "extra_bed", 
                    className: "text-center",
                    searchable: false,
                    render: function(data) {
                        if (data > 0) {
                            return `<span class="badge rounded-pill bg-primary shadow-sm" style="font-size: 11px;">+${data}</span>`;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                // 7. Extra Breakfast
                { 
                    data: "extra_breakfast", 
                    name: "extra_breakfast", 
                    className: "text-center",
                    searchable: false,
                    render: function(data) {
                        if (data > 0) {
                            return `<span class="badge rounded-pill bg-warning text-dark shadow-sm" style="font-size: 11px;">+${data}</span>`;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },

                // 8. Sarapan (Regular)
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

                // 9. Total Harga
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

                // 10. Sisa Bayar
                { 
                    name: "transactions.paid_amount", 
                    data: "remaining_payment",
                    className: "text-center",
                    render: function(data) {
                        let formatted = new Intl.NumberFormat('id-ID', { 
                            style: 'currency', 
                            currency: 'IDR', 
                            minimumFractionDigits: 0 
                        }).format(data);

                        if (data > 0) {
                            return `<span class="badge rounded-pill" style="background-color: #F2C2B8; color: #50200C; 
                                            font-size: 11px; padding: 6px 12px; font-weight: 800; border: 1px solid #E57373;">
                                            ${formatted}
                                            </span>`;
                        }
                        
                        return `<span class="badge rounded-pill" style="background-color: #A8D5BA; color: #50200C; 
                                        font-size: 11px; padding: 6px 12px; font-weight: 800; border: 1px solid #81C784;">
                                        Lunas
                                        </span>`;
                    }
                },

                // 11. Status
                { 
                    name: "transactions.status", 
                    data: "status",
                    className: "text-center",
                    render: function(data) {
                        return `<span class="badge px-3 py-1 rounded-pill" style="background-color: #8FB8E1;
                        color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;">${data}</span>`;
                    }
                },
                // 12. Aksi
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: "text-center align-middle",
                    render: function(id, type, row) {
                        let customerName = row.customer_name ? row.customer_name.replace(/"/g, '&quot;') : '-'; 
                        let roomNumber = row.room_info.number;
                        // [MODIFIED] Added data-remaining attribute
                        let remaining = row.remaining_payment; 

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
                                        data-room="${roomNumber}"
                                        data-remaining="${remaining}">
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

        // Event: Tombol Edit
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

        // Event: Submit Form Edit
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
                    let modalEl = document.getElementById('editCheckinModal');
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    }
                    
                    let iconType = response.status || 'success';
                    let titleText = 'Berhasil Diperbarui!';
                    
                    if (iconType === 'warning') titleText = 'Perhatian: Kurang Bayar!';
                    else if (iconType === 'info') titleText = 'Info Refund';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: iconType,
                            title: titleText,
                            text: response.message,
                            iconColor: '#50200C', 
                            customClass: {
                                title: 'swal-title-brown',
                                htmlContainer: 'swal-text-brown',
                                confirmButton: 'swal-btn-blue'
                            }
                        });
                    } else {
                        alert(titleText + "\n" + response.message); 
                    }
                    
                    table.ajax.reload(null, false);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalContent);
                    let msg = "Gagal menyimpan perubahan.";
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: msg,
                            customClass: {
                                title: 'swal-title-brown',
                                confirmButton: 'swal-btn-blue'
                            }
                        });
                    } else {
                         alert(msg);
                    }
                }
            });
        });

        // Event: Tombol Check Out [MODIFIED LOGIC]
        $(document).on('click', '.btn-checkout', function() {
            checkoutId = $(this).data('id');
            let name = $(this).data('name');
            let room = $(this).data('room');
            let remaining = parseFloat($(this).data('remaining')); // Get remaining balance

            $('#checkoutCustomerName').text(name);
            $('#checkoutRoomNumber').text(room);

            // Logic to update Modal Content based on payment status
            let warningContainer = $('#checkoutWarningContainer'); // We need to add this ID to view
            
            // If the element doesn't exist (first time logic or if view not updated yet), let's handle it manually or assume view has the alert div
            // Let's modify the modal body content dynamically
            let modalBody = $('#checkoutModal .modal-body');
            
            // Remove any existing dynamic alerts first to avoid duplicates
            modalBody.find('.dynamic-alert').remove();

            if (remaining > 0) {
                let formattedRemaining = new Intl.NumberFormat('id-ID', { 
                    style: 'currency', 
                    currency: 'IDR', 
                    minimumFractionDigits: 0 
                }).format(remaining);

                let alertHtml = `
                    <div class="alert alert-light dynamic-alert border-danger d-flex align-items-center mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle me-3 fa-2x" style="color: #A94442"></i>
                        <div class="text-start">
                            <strong class="d-block" style="color: #A94442">BELUM LUNAS!</strong>
                            <small style="color: #50200C">Tamu memiliki sisa tagihan sebesar <strong style="color: #A94442; font-size: 1.1em;">${formattedRemaining}</strong>.</small>
                        </div>
                    </div>
                `;
                // Insert after the room number paragraph
                $('#checkoutRoomNumber').parent().after(alertHtml);
            }

            let modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        });

        // Event: Konfirmasi Check Out
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
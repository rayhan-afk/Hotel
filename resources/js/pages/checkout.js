$(function () {
    const currentRoute = window.location.pathname;
    
    // Cek route agar script ini hanya jalan di halaman yang sesuai
    // Pastikan URL di browser mengandung kata 'checkout'
    if (!currentRoute.includes("transaction/checkout") && !currentRoute.includes("transaction/check-out")) return;

    const tableElement = $("#checkout-table");

    if (tableElement.length > 0) {
        const table = tableElement.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                // Pastikan URL ini sesuai dengan Route di web.php
                // Biasanya /transaction/checkout (tanpa strip) sesuai nama controller
                url: "/transaction/checkout", 
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
                                <span class="fw-bold text-dark">${data.number}</span>
                                <span class="text-muted small">${data.type}</span>
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
                    name: "transactions.id", // Dummy
                    data: "breakfast",
                    className: "text-center",
                    orderable: false,
                    render: function(data) {
                        return data == 1 
                            ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Yes</span>' 
                            : '<span class="badge bg-secondary">No</span>';
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
                        return `<span class="badge bg-success px-3 py-1 rounded-pill">${data}</span>`;
                    }
                },
                // 9. Aksi (Proses Checkout)
                {
                    data: 'raw_id',
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function(id) {
                        return `
                            <button class="btn btn-sm btn-warning text-dark btn-checkout rounded-pill shadow-sm fw-bold px-3" 
                                    data-id="${id}" 
                                    data-bs-toggle="tooltip" 
                                    title="Proses Checkout">
                                <i class="fas fa-sign-out-alt me-1"></i> Checkout
                            </button>
                        `;
                    }
                }
            ],
            order: [[4, 'asc']], // Urutkan Check-out terdekat
            drawCallback: function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });

        // === EVENT CHECKOUT (DIPERBAIKI) ===
        // Menggunakan $(document).on agar bisa mendeteksi tombol di dalam Datatable
        $(document).on('click', '.btn-checkout', function() {
            let id = $(this).data('id');
            // Ambil CSRF token dengan aman
            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            Swal.fire({
                title: 'Konfirmasi Checkout',
                text: "Status akan berubah menjadi 'Cleaning' dan jam keluar akan dicatat sekarang.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Checkout Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan Loading
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    // Kirim Request AJAX
                    $.ajax({
                        url: `/transaction/checkout/${id}`, // Pastikan route ini benar di web.php
                        type: 'POST',
                        data: { 
                            _token: csrfToken,
                            _method: 'POST' // Tegaskan method POST
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            table.ajax.reload(null, false); // Reload tabel tanpa reset paging
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            let errorMsg = 'Terjadi kesalahan sistem.';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire('Gagal!', errorMsg, 'error');
                        }
                    });
                }
            });
        });
    }
});
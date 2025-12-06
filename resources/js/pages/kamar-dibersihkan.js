$(function () {
    const currentRoute = window.location.pathname;
    if (!currentRoute.includes("room-info/cleaning")) return;

    const tableSelector = "#cleaning-table";
    let selectedRoomId = null; // Menyimpan ID kamar yang sedang dipilih di modal

    if ($(tableSelector).length > 0) {
        const table = $(tableSelector).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/room-info/cleaning",
                type: "GET",
                error: function (xhr, status, error) {
                    console.error("Datatable Error:", error);
                },
            },
            columns: [
                { 
                    data: 'DT_RowIndex', 
                    name: 'DT_RowIndex', 
                    orderable: false, 
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: 'number', 
                    name: 'number',
                    className: "align-middle",
                    render: function(data) {
                        return `<span class="fw-bold text-dark fs-5" style="color: #50200C;">${data}</span>`;
                    }
                },
                { 
                    data: 'type_name', 
                    name: 'type.name',
                    className: "align-middle"
                },
                { 
                    data: 'status', 
                    name: 'status',
                    className: "text-center align-middle",
                    render: function() {
                        return '<span class="badge px-3 py-2 rounded-pill" style="background-color: #FAE8A4; color: #50200C; font-size: 10px; padding: 6px 12px; font-weight: 700;"><i class="fas fa-broom me-1"></i> Cleaning</span>';
                    }
                },
                { 
                    data: 'action', // Action ini berisi tombol HTML dari Controller
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: "text-center align-middle",
                    render: function(data, type, row) {
                        // Kita Override render tombol disini agar bisa menyisipkan Data Attribute untuk Modal
                        // row.id, row.number, row.type_name tersedia dari JSON response
                        return `
                            <button class="btn btn-success btn-finish-cleaning shadow-sm px-3 fw-bold" 
                                    data-id="${row.id}"
                                    data-number="${row.number}"
                                    data-type="${row.type_name || row.type.name}">
                                <i class="fas fa-check me-1"></i>Selesai
                            </button>
                        `;
                    }
                },
            ],
            language: {
                emptyTable: "Tidak ada kamar yang sedang dibersihkan saat ini.",
                processing: "Memuat data...",
                zeroRecords: "Data tidak ditemukan"
            }
        });

        // 1. Event: Klik Tombol Selesai (MUNCULKAN MODAL)
        $(document).on('click', '.btn-finish-cleaning', function() {
            selectedRoomId = $(this).data('id');
            let roomNumber = $(this).data('number');
            let roomType = $(this).data('type');

            // Isi Data ke dalam Modal
            $('#cleaningRoomNumber').text(roomNumber);
            $('#cleaningRoomType').text(roomType);

            // Tampilkan Modal
            let modal = new bootstrap.Modal(document.getElementById('finishCleaningModal'));
            modal.show();
        });

        // 2. Event: Konfirmasi di dalam Modal (EKSEKUSI AJAX)
        $('#btn-confirm-finish').on('click', function() {
            if(!selectedRoomId) return;

            let btn = $(this);
            let originalContent = btn.html();
            let csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Loading state
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Proses...');

            $.ajax({
                url: `/room-info/cleaning/${selectedRoomId}/finish`,
                type: 'POST',
                data: { _token: csrfToken },
                success: function(response) {
                    // Tutup modal
                    $('#finishCleaningModal').modal('hide');
                    
                    // Tampilkan pesan sukses sebentar (opsional, atau pakai toast)
                    // alert(response.message); 

                    // Reload tabel otomatis
                    table.ajax.reload();
                    
                    // Reset tombol
                    btn.prop('disabled', false).html(originalContent);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalContent);
                    alert("Gagal memperbarui status kamar: " + (xhr.responseJSON ? xhr.responseJSON.message : "Error Sistem"));
                }
            });
        });
    }
});
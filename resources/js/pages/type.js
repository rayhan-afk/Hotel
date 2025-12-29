$(function() {
    const currentRoute = window.location.pathname;
    if(!currentRoute.startsWith('/type')) return

    const datatable = $("#type-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `/type`,
            type: 'GET',
            error: function(xhr, status, error) {
                console.error("Datatable Error:", error);
            }
        },
        "columns": [{
                "name": "number",
                "data": "number"
            },
            {
                "name": "name",
                "data": "name"
            },
            {
                "name": "information",
                "data": "information"
            },
            {
                "name": "id",
                "data": "id",
                "width": "150px",
                "render": function(typeId, type, row) {
                    // Kita perlu nama tipe untuk judul modal, ambil dari row.name
                    const typeName = row.name ? row.name.replace(/'/g, "\\'") : '';
                    
                    return `
                        <div class="d-flex justify-content-center gap-1">
                            
                            <button onclick="window.openPriceModal(${typeId}, '${typeName}')" 
                                    class="btn btn-sm rounded shadow-sm border btn-price"
                                    style="background-color: #ffffff; color: #50200C;"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    title="Atur Harga Spesial">
                                <i class="fas fa-dollar-sign"></i>
                            </button>

                            <button class="btn btn-light btn-sm rounded shadow-sm border"
                                data-action="edit-type" data-type-id="${typeId}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit type">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <form class="delete-type p-0 m-0" method="POST"
                                id="delete-type-form-${typeId}"
                                action="/type/${typeId}">
                                <input type="hidden" name="_method" value="DELETE">
                                <a class="btn btn-light btn-sm rounded shadow-sm border delete"
                                    href="#" type-id="${typeId}" type-role="type" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Delete type">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </form>

                        </div>
                    `;
                }
            }
        ]
    });

    const modal = new bootstrap.Modal($("#main-modal"), {
        backdrop: true,
        keyboard: true,
        focus: true
    })

    // Inisialisasi Modal Harga
    const priceModalEl = document.getElementById('priceModal');
    let priceModal;
    if (priceModalEl) {
        priceModal = new bootstrap.Modal(priceModalEl, {
            backdrop: 'static',
            keyboard: false
        });
    }

    // =========================================================================
    // LOGIC BARU: MODAL HARGA & TOMBOL SIMPAN
    // =========================================================================

    // 1. Fungsi Global untuk membuka Modal
    window.openPriceModal = function(typeId, typeName) {
        $('#price_type_id').val(typeId);
        
        // Update Judul Modal biar lebih informatif
        $('#priceModal .modal-title').html(`<i class="fas fa-tags me-2"></i>Atur Harga: ${typeName}`);
        
        // Tampilkan loading state
        $('#priceTableBody').html('<tr><td colspan="3" class="text-center p-4"><i class="fas fa-spinner fa-spin me-2"></i>Mengambil data harga...</td></tr>');
        
        if (priceModal) priceModal.show();

        // [FIX URL] Gunakan /type/ (singular) sesuai route Laravel standar
        $.get(`/type/get-prices/${typeId}`, function(data) {
            let html = '';
            
            if(data.length > 0) {
                data.forEach(function(item) {
                    let weekdayVal = (item.weekday !== null && item.weekday !== '') ? item.weekday : '';
                    let weekendVal = (item.weekend !== null && item.weekend !== '') ? item.weekend : '';

                    html += `
                        <tr>
                            <td class="fw-bold ps-3 align-middle" style="background-color: #F7F3E4; color: #50200C;">
                                ${item.group}
                            </td>
                            <td class="p-2" style="background-color: #F7F3E4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text text-white border-0" 
                                          style="background-color: #50200C;">Rp</span>
                                
                                    <input type="number" class="form-control border-secondary" 
                                        style="border-color: #ced4da;"
                                        name="prices[${item.group}][weekday]" 
                                        value="${weekdayVal}" 
                                        placeholder="Default">
                                </div>
                            </td>
                            <td class="p-2" style="background-color: #F7F3E4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text text-white border-0" 
                                          style="background-color: #50200C;">Rp</span>
                                
                                    <input type="number" class="form-control border-secondary" 
                                        style="border-color: #ced4da;"
                                        name="prices[${item.group}][weekend]" 
                                        value="${weekendVal}" 
                                        placeholder="Default">
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center p-4">Tidak ada data grup customer.</td></tr>';
            }
            
            $('#priceTableBody').html(html);
        }).fail(function(err) {
             console.error(err);
             $('#priceTableBody').html('<tr><td colspan="3" class="text-center text-danger p-4">Gagal mengambil data. Pastikan Route "/type/get-prices/{id}" sudah dibuat.</td></tr>');
        });
    };

    // 2. Event Listener Tombol Simpan Harga
    $('.btn-modal-save').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

        let formData = $('#priceForm').serialize();
        
        // [FIX URL] Gunakan /type/store-prices
        $.ajax({
            url: '/type/store-prices', 
            type: 'POST',
            data: formData,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(response) {
                if (priceModal) priceModal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.success,
                    showConfirmButton: false,
                    timer: 1500,
                    iconColor: '#50200C',
                    customClass: { title: 'swal-title-brown' }
                });
            },
            error: function(err) {
                console.log(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat menyimpan data.',
                    iconColor: '#50200C',
                    customClass: { title: 'swal-title-brown' }
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // =========================================================================
    // LOGIC LAMA (ADD/EDIT/DELETE TYPE) - TETAP AMAN
    // =========================================================================

    $(document).on('click', '.delete', function() {
        var type_id = $(this).attr('type-id');
        
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
                confirmButton: "text-50200C btn btn-success me-2",
                cancelButton: "text-50200C btn btn-danger",
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#delete-type-form-${type_id}`).submit();
            }
        })
    }).on('click', '#add-button', async function() {
        modal.show()
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
        $('#main-modal .modal-body').html(`Fetching data`)

        const response = await $.get(`/type/create`);
        if (!response) return

        $('#main-modal .modal-title').text('Tambah Tipe Kamar')
        $('#main-modal .modal-body').html(response.view)
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
        $('.select2').select2();
    }).on('click', '#btn-modal-save', function() {
        $('#form-save-type').submit()
    }).on('submit', '#form-save-type', async function(e) {
        e.preventDefault();
        CustomHelper.clearError()
        $('#btn-modal-save').attr('disabled', true)
        try {
            const response = await $.ajax({
                url: $(this).attr('action'),
                data: $(this).serialize(),
                method: $(this).attr('method'),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })

            if (!response) return

            Swal.fire({
                position: 'center',
                icon: 'success',
                title: response.message,
                showConfirmButton: false,
                timer: 1500,
                iconColor: "#50200C",
                customClass: {
                    title: "swal-title-brown"
                }
            })

            modal.hide()
            datatable.ajax.reload()
        } catch (e) {
            if (e.status === 422) {
                console.log(e)
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: e.responseJSON.message,
                })
                CustomHelper.errorHandlerForm(e)
            }
        } finally {
            $('#btn-modal-save').attr('disabled', false)
        }
    }).on('click', '[data-action="edit-type"]', async function() {
        modal.show()
        $("#btn-modal-save").text("Simpan").attr("disabled", true);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
        $('#main-modal .modal-body').html(`Fetching data`)

        const typeId = $(this).data('type-id')
        const response = await $.get(`/type/${typeId}/edit`);
        if (!response) return

        $('#main-modal .modal-title').text('Edit tipe Kamar')
        $('#main-modal .modal-body').html(response.view)
        $("#btn-modal-save").text("Simpan").attr("disabled", false);
        $('button[data-bs-dismiss="modal"]:not(.btn-close)').text("Batal");
        $('.btn-close[data-bs-dismiss="modal"]').text('');
        $('.select2').select2();
    }).on('submit', '.delete-type', async function(e) {
        e.preventDefault()

        try {
            const response = await $.ajax({
                url: $(this).attr('action'),
                data: $(this).serialize(),
                method: $(this).attr('method'),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            })

            if (!response) return

            Swal.fire({
                position: 'center',
                icon: 'success',
                title: response.message,
                showConfirmButton: false,
                timer: 1500,
                iconColor: '#50200C',
                customClass: { title: 'swal-title-brown' }
            })

            datatable.ajax.reload()
        } catch (e) {
            if(e && e.responseJSON && e.responseJSON.message) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: e.responseJSON.message,
                    showConfirmButton: false,
                    timer: 1500,
                    iconColor: "#50200C",
                    customClass: { title: "swal-title-brown" }
                })
            }
        }
    })
});
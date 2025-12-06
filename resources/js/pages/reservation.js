$(function () {
    const reservationForm = $('#reservation-form');
    if (reservationForm.length === 0) return;

    // Ambil data dari atribut yang baru kita tambahkan di atas
    const roomPrice = parseFloat(reservationForm.data('room-price')); 
    const dayDifference = parseInt(reservationForm.data('duration'));
    const breakfastRate = 140000;

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0 
        }).format(angka);
    }

    $('#breakfast_select').on('change', function() {
        const isBreakfast = $(this).val() === 'Yes';
        
        // 1. Hitung Dasar
        const roomTotalCost = roomPrice * dayDifference;
        const breakfastTotal = isBreakfast ? (breakfastRate * dayDifference) : 0;
        
        // 2. Subtotal
        const subTotal = roomTotalCost + breakfastTotal;

        // 3. Pajak 10%
        const taxAmount = subTotal * 0.10;

        // 4. Total Akhir
        const grandTotal = subTotal + taxAmount;

        // Update UI
        if (isBreakfast) {
            $('#row_breakfast').fadeIn();
            $('#display_breakfast_total').text(formatRupiah(breakfastTotal));
        } else {
            $('#row_breakfast').fadeOut();
        }

        $('#display_tax').text(formatRupiah(taxAmount));
        $('#display_total_price').text(formatRupiah(grandTotal));
        $('#input_total_price').val(grandTotal);
    });

    // Panggil event change agar hitungan muncul saat halaman dimuat
    $('#breakfast_select').trigger('change');
});
$(function () {
    // Definisi Konstanta & Elemen
    const breakfastRate = 140000; // Harga sarapan per hari
    const taxRate = 0.10;         // Pajak 10%
    
    // Elemen Input
    const elmRoom = $('#room_id');
    const elmCustomer = $('#customer_id'); // Pastikan ID ini ada di select customer
    const elmCheckIn = $('#check_in');
    const elmCheckOut = $('#check_out');
    const elmBreakfast = $('#breakfast_select'); // Yes/No select
    
    // Elemen Display (Output)
    const displayTotal = $('#display_total_price'); // Teks H3 Total
    const displayTax = $('#display_tax');           // Teks Pajak
    const displayBreakfast = $('#display_breakfast_total'); // Teks Total Sarapan
    const rowBreakfast = $('#row_breakfast');       // Baris tabel sarapan (untuk show/hide)
    const inputTotal = $('#input_total_price');     // Input hidden untuk submit form

    // Helper Format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0 
        }).format(angka);
    }

    // Helper Hitung Durasi Hari (JS Side)
    function getDaysDifference(date1, date2) {
        const oneDay = 24 * 60 * 60 * 1000; // Jam * Menit * Detik * Milidetik
        const firstDate = new Date(date1);
        const secondDate = new Date(date2);
        
        if (isNaN(firstDate) || isNaN(secondDate)) return 0;
        
        // Hitung selisih hari (Absolute biar gak minus)
        const diffDays = Math.round(Math.abs((firstDate - secondDate) / oneDay));
        return diffDays; // Misal checkin tgl 1, checkout tgl 2 = 1 malam
    }

    // --- FUNGSI UTAMA: HITUNG TOTAL ---
    function calculateTotal() {
        const roomId = elmRoom.val();
        const customerId = elmCustomer.val();
        const checkIn = elmCheckIn.val();
        const checkOut = elmCheckOut.val();
        const isBreakfast = elmBreakfast.val() === 'Yes';

        // Validasi: Jangan hitung kalau data belum lengkap
        if (!roomId || !checkIn || !checkOut) {
            return; 
        }

        // 1. Panggil Server untuk Hitung Harga Kamar (Sultan Mode)
        // Kita kirim data ke Controller untuk dicek (Weekday vs Weekend & Grup Customer)
        $.ajax({
            url: '/transaction/count-payment', // Pastikan route ini ada di web.php
            type: 'GET',
            data: {
                room_id: roomId,
                customer_id: customerId,
                check_in: checkIn,
                check_out: checkOut
            },
            success: function(response) {
                // response.total adalah TOTAL HARGA KAMAR MURNI (sudah dikali malam & aturan weekend)
                let roomTotalCost = parseFloat(response.total); 
                
                // 2. Hitung Durasi (untuk pengali sarapan)
                let duration = getDaysDifference(checkIn, checkOut);
                if (duration < 1) duration = 1; // Minimal 1 malam

                // 3. Hitung Sarapan
                let breakfastTotal = 0;
                if (isBreakfast) {
                    breakfastTotal = breakfastRate * duration;
                    rowBreakfast.fadeIn();
                    displayBreakfast.text(formatRupiah(breakfastTotal));
                } else {
                    rowBreakfast.fadeOut();
                    displayBreakfast.text(formatRupiah(0));
                }

                // 4. Hitung Subtotal & Pajak
                const subTotal = roomTotalCost + breakfastTotal;
                const taxAmount = subTotal * taxRate;
                const grandTotal = subTotal + taxAmount;

                // 5. Update Tampilan
                displayTax.text(formatRupiah(taxAmount));
                displayTotal.text(formatRupiah(grandTotal));
                
                // Update Input Hidden (Penting untuk dikirim ke database saat save)
                inputTotal.val(grandTotal);
            },
            error: function(err) {
                console.error("Gagal menghitung harga:", err);
            }
        });
    }

    // --- Event Listeners ---
    // Setiap kali user mengubah salah satu input ini, hitung ulang!
    
    // Untuk Select2, gunakan event 'select2:select' atau 'change'
    elmRoom.on('change', calculateTotal);
    elmCustomer.on('change', calculateTotal); 
    
    // Untuk input date & select biasa
    elmCheckIn.on('change', calculateTotal);
    elmCheckOut.on('change', calculateTotal);
    elmBreakfast.on('change', calculateTotal);

    // Panggil sekali saat halaman dimuat (untuk edit mode atau refresh)
    calculateTotal();
});
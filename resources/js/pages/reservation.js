$(function () {
    // Definisi Konstanta & Elemen
    const breakfastRate = 140000; // Harga sarapan per hari
    // const taxRate = 0.10; <--- DIHAPUS (Tidak ada pajak)
    
    // Elemen Input
    const elmRoom = $('#room_id');
    const elmCustomer = $('#customer_id'); 
    const elmCheckIn = $('#check_in');
    const elmCheckOut = $('#check_out');
    const elmBreakfast = $('#breakfast_select'); 
    
    // Elemen Display (Output)
    const displayTotal = $('#display_total_price'); 
    const displayTax = $('#display_tax');           
    const displayBreakfast = $('#display_breakfast_total'); 
    const rowBreakfast = $('#row_breakfast');       
    const inputTotal = $('#input_total_price');     

    // Helper Format Rupiah
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0 
        }).format(angka);
    }

    // Helper Hitung Durasi Hari (JS Side)
    function getDaysDifference(date1, date2) {
        const oneDay = 24 * 60 * 60 * 1000; 
        const firstDate = new Date(date1);
        const secondDate = new Date(date2);
        
        if (isNaN(firstDate) || isNaN(secondDate)) return 0;
        
        const diffDays = Math.round(Math.abs((firstDate - secondDate) / oneDay));
        return diffDays; 
    }

    // --- FUNGSI UTAMA: HITUNG TOTAL ---
    function calculateTotal() {
        const roomId = elmRoom.val();
        const customerId = elmCustomer.val();
        const checkIn = elmCheckIn.val();
        const checkOut = elmCheckOut.val();
        const isBreakfast = elmBreakfast.val() === 'Yes';

        // Validasi
        if (!roomId || !checkIn || !checkOut) {
            return; 
        }

        // 1. Panggil Server
        $.ajax({
            url: '/transaction/count-payment', 
            type: 'GET',
            data: {
                room_id: roomId,
                customer_id: customerId,
                check_in: checkIn,
                check_out: checkOut
            },
            success: function(response) {
                // response.total adalah TOTAL HARGA KAMAR MURNI
                let roomTotalCost = parseFloat(response.total); 
                
                // 2. Hitung Durasi 
                let duration = getDaysDifference(checkIn, checkOut);
                if (duration < 1) duration = 1; 

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

                // 4. Hitung Subtotal & Grand Total (TANPA PAJAK)
                const subTotal = roomTotalCost + breakfastTotal;
                
                // const taxAmount = subTotal * taxRate; <--- DIHAPUS
                // const grandTotal = subTotal + taxAmount; <--- DIUBAH

                const grandTotal = subTotal; // Langsung Subtotal

                // 5. Update Tampilan
                if(displayTax.length) {
                    displayTax.text(formatRupiah(0)); // Set 0 jika elemen tax masih ada di HTML
                }
                
                displayTotal.text(formatRupiah(grandTotal));
                
                // Update Input Hidden
                inputTotal.val(grandTotal);
            },
            error: function(err) {
                console.error("Gagal menghitung harga:", err);
            }
        });
    }

    // --- Event Listeners ---
    elmRoom.on('change', calculateTotal);
    elmCustomer.on('change', calculateTotal); 
    elmCheckIn.on('change', calculateTotal);
    elmCheckOut.on('change', calculateTotal);
    elmBreakfast.on('change', calculateTotal);

    calculateTotal();
});
/**
 * POS Logic - Point of Sales
 * File: resources/js/pages/POS.js
 * Status: FIXED (Category Filter Added)
 */

(function () {
    ("use strict");

    console.log("🛒 POS System Loaded - Fixed Version");

    let cart = [];
    let currentTotal = 0;

    // Ambil Container Menu
    const menuContainer = document.getElementById("menuContainer");

    // ============================================
    // 1. ADD TO CART (Global Function)
    // ============================================
    window.addToCart = function (id, name, price) {
        id = parseInt(id);
        price = parseFloat(price);

        const existingItem = cart.find((item) => item.id === id);
        if (existingItem) {
            existingItem.qty += 1;
            if (typeof toastr !== "undefined") toastr.info(`${name} (+1)`);
        } else {
            cart.push({ id: id, name: name, price: price, qty: 1 });
            if (typeof toastr !== "undefined")
                toastr.success(`${name} masuk keranjang`);
        }
        renderCart();
    };

    // ============================================
    // [PENTING] EVENT LISTENER CLICK (Dikembalikan)
    // ============================================
    if (menuContainer) {
        menuContainer.addEventListener("click", function (e) {
            const card = e.target.closest(".product-card");
            if (!card) return;
            if (card.hasAttribute("onclick")) return;

            const id = card.getAttribute("data-menu-id");
            const name = card.getAttribute("data-menu-name");
            const price = card.getAttribute("data-menu-price");

            if (id && name && price) {
                window.addToCart(id, name, price);
            }
        });
    }

    // ============================================
    // 2. RENDER CART
    // ============================================
    window.renderCart = function () {
        const cartContainer = document.getElementById("cartItems");
        if (!cartContainer) return;

        if (cart.length === 0) {
            cartContainer.innerHTML = `<div class="text-center text-muted mt-5"><i class="fas fa-shopping-cart fa-2x mb-2" style="opacity: 0.3"></i><p>Keranjang kosong</p></div>`;
            updateTotals(0);
            return;
        }

        let htmlString = "";
        let subtotal = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            subtotal += itemTotal;

            htmlString += `
                <div class="card border-0 shadow-sm mb-2">
                    <div class="card-body p-2 d-flex align-items-center">
                        <div class="d-flex flex-column align-items-center me-3">
                             <i class="fas fa-chevron-up small text-muted" style="cursor:pointer" onclick="updateQty(${index}, 1)"></i>
                             <span class="fw-bold my-1">${item.qty}</span>
                             <i class="fas fa-chevron-down small text-muted" style="cursor:pointer" onclick="updateQty(${index}, -1)"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-truncate" style="max-width: 140px;">${
                                item.name
                            }</div>
                            <small class="text-muted">@ ${formatRupiah(
                                item.price
                            )}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${formatRupiah(
                                itemTotal
                            )}</div>
                            <i class="fas fa-trash-alt text-danger small mt-1" style="cursor:pointer" onclick="removeItem(${index})"></i>
                        </div>
                    </div>
                </div>`;
        });

        cartContainer.innerHTML = htmlString;
        updateTotals(subtotal);
    };

    // ============================================
    // 3. FUNGSI UPDATE & HAPUS
    // ============================================
    window.updateQty = function (index, change) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    };

    window.removeItem = function (index) {
        cart.splice(index, 1);
        renderCart();
    };

    window.clearCart = function () {
        if (confirm("Reset keranjang?")) {
            cart = [];
            renderCart();
        }
    };

    // ============================================
    // 4. UPDATE TOTAL DISPLAY
    // ============================================
    function updateTotals(subtotal) {
        currentTotal = subtotal;

        const els = ["totalDisplay", "modalTotalDisplay", "subtotalDisplay"];
        els.forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.innerText = formatRupiah(currentTotal);
        });
    }

    // ============================================
    // 5. FILTER MENU
    // ============================================
    window.filterMenu = function () {
        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");

        if (!searchInput || !categoryFilter) return;

        // Ambil nilai Search & Filter (Lowercase & Trim agar tidak sensitif huruf besar/kecil)
        const searchValue = searchInput.value.toLowerCase().trim();
        const categoryValue = categoryFilter.value.toLowerCase().trim();

        const items = document.querySelectorAll(".menu-item");

        items.forEach((item) => {
            // Ambil data atribut (Lowercase & Trim juga)
            const name = (item.getAttribute("data-name") || "").toLowerCase();
            const category = (item.getAttribute("data-category") || "")
                .toLowerCase()
                .trim();

            // Cek Pencocokan
            const matchSearch = name.includes(searchValue);
            // Bandingkan 'all' ATAU nilai string yang sudah disamakan formatnya
            const matchCategory =
                categoryValue === "all" || category === categoryValue;

            if (matchSearch && matchCategory) {
                // Gunakan string kosong agar kembali ke display default CSS (biar grid tidak rusak)
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    };

    // ============================================
    // 5.5. SET KATEGORI (Fungsi Baru untuk Tombol Kategori)
    // ============================================
    window.setCategory = function (category, btnElement) {
        // 1. Reset style semua tombol agar tidak aktif (outline)
        const buttons = document.querySelectorAll(".cat-btn");
        buttons.forEach((btn) => {
            btn.classList.remove("btn-primary", "active"); // Hapus warna solid
            btn.classList.add("btn-outline-primary"); // Tambah outline
        });

        // 2. Set style tombol yang diklik jadi aktif (solid)
        if (btnElement) {
            btnElement.classList.remove("btn-outline-primary");
            btnElement.classList.add("btn-primary", "active");
        }

        // 3. Update nilai hidden input dengan kategori yang dipilih
        const filterInput = document.getElementById("categoryFilter");
        if (filterInput) {
            filterInput.value = category;

            // 4. Panggil fungsi filter utama untuk memperbarui tampilan
            window.filterMenu();
        } else {
            console.error("Input hidden 'categoryFilter' tidak ditemukan!");
        }
    };

    // ============================================
    // 6. LOGIKA BAYAR & KEMBALIAN
    // ============================================
    window.calculateChange = function () {
        const payInput = document.getElementById("payAmount");
        if (!payInput) return;

        const payAmount = parseFloat(payInput.value || 0);
        const change = payAmount - currentTotal;
        const display = document.getElementById("changeDisplay");

        if (display) {
            if (change < 0) {
                display.innerText = "Kurang " + formatRupiah(Math.abs(change));
                display.className = "fw-bold text-danger";
            } else {
                display.innerText = formatRupiah(change);
                display.className = "fw-bold text-success";
            }
        }
    };

    // LOGIKA PROSES PEMBAYARAN (FIX: Clear Cart on Success)
    window.processPayment = function () {
        const payInput = document.getElementById("payAmount").value;
        const payAmount = payInput ? parseFloat(payInput) : 0;

        if (cart.length === 0) return alert("Keranjang kosong!");
        if (payAmount < currentTotal) return alert("Uang pembayaran kurang!");

        const storeRoute = window.posConfig ? window.posConfig.storeRoute : "";
        const csrfToken = window.posConfig ? window.posConfig.csrfToken : "";

        // Simpan nilai transaksi saat ini agar tidak hilang saat cart direset
        const savedTotal = currentTotal;
        const savedChange = payAmount - currentTotal;

        // Ubah tombol jadi loading
        const btnProcess = document.querySelector(
            "#paymentModal .modal-footer button.text-white"
        );
        const originalText = btnProcess ? btnProcess.innerText : "Proses Bayar";
        if (btnProcess) {
            btnProcess.innerText = "Memproses...";
            btnProcess.disabled = true;
        }

        const dataToSend = {
            cart: cart,
            total_amount: currentTotal,
            pay_amount: payAmount,
            payment_method: "Tunai",
        };

        fetch(storeRoute, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(dataToSend),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "success") {
                    // [FIX] Reset Keranjang LANGSUNG saat server bilang sukses
                    cart = [];
                    renderCart();
                    document.getElementById("payAmount").value = "";
                    document.getElementById("changeDisplay").innerText = "Rp 0";

                    // Tampilkan Modal Sukses
                    showSuccessState(
                        data.invoice,
                        savedTotal,
                        payAmount,
                        savedChange
                    );
                } else {
                    alert("Gagal: " + data.message);
                    if (btnProcess) {
                        btnProcess.innerText = originalText;
                        btnProcess.disabled = false;
                    }
                }
            })
            .catch((err) => {
                console.error(err);
                alert("Terjadi kesalahan sistem.");
                if (btnProcess) {
                    btnProcess.innerText = originalText;
                    btnProcess.disabled = false;
                }
            });
    };

    // FUNGSI TAMPILAN SUKSES
    function showSuccessState(invoice, total, bayar, kembalian) {
        const modalBody = document.querySelector("#paymentModal .modal-body");
        const modalFooter = document.querySelector(
            "#paymentModal .modal-footer"
        );
        const modalTitle = document.querySelector("#paymentModal .modal-title");

        if (!modalBody || !modalFooter) return;

        modalTitle.innerText = "Transaksi Berhasil!";
        modalTitle.classList.add("text-success");

        modalBody.innerHTML = `
            <div class="text-center py-3">
                <div style="width: 70px; height: 70px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-check fa-3x text-success"></i>
                </div>
                <h5 class="mt-3 fw-bold">Pembayaran Sukses!</h5>
                <p class="text-muted mb-3 small">Invoice: ${invoice}</p>

                <div class="card bg-light border-0 p-3 text-start small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total:</span> <span class="fw-bold">${formatRupiah(
                            total
                        )}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Bayar:</span> <span>${formatRupiah(bayar)}</span>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-2">
                        <span class="fw-bold text-success">Kembalian:</span>
                        <span class="fw-bold text-success">${formatRupiah(
                            kembalian
                        )}</span>
                    </div>
                </div>
            </div>
        `;

        modalFooter.innerHTML = `
            <button type="button" class="btn btn-outline-secondary" onclick="window.open('/pos/print/${invoice}', '_blank', 'width=400,height=600')">
                <i class="fas fa-print me-1"></i>Cetak
            </button>
            <button type="button" class="btn btn-success text-white fw-bold px-4" onclick="finishTransaction()">
                Selesai
            </button>
        `;
    }

    // FUNGSI RESET SYSTEM TANPA RELOAD (SOFT RESET)
    window.finishTransaction = function () {
        // Tutup Modal
        const modalEl = document.getElementById("paymentModal");
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            const closeBtn = document.querySelector("#paymentModal .btn-close");
            if (closeBtn) closeBtn.click();
        }
    };

    // Helper: Reset tampilan modal ke form awal
    function resetModalUI() {
        const modalTitle = document.querySelector("#paymentModal .modal-title");
        const modalBody = document.querySelector("#paymentModal .modal-body");
        const modalFooter = document.querySelector(
            "#paymentModal .modal-footer"
        );

        if (!modalBody) return;

        modalTitle.innerText = "Konfirmasi Pembayaran";
        modalTitle.classList.remove("text-success");

        modalBody.innerHTML = `
            <div class="text-center mb-4">
                <small class="text-muted">Total Tagihan</small>
                <h3 class="fw-bold mb-0" style="color: #50200C;" id="modalTotalDisplay">Rp 0</h3>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small">Uang Diterima</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" id="payAmount" class="form-control" placeholder="Masukkan nominal..." oninput="calculateChange()">
                </div>
            </div>
            <div class="d-flex justify-content-between alert alert-light border">
                <span class="fw-bold">Kembalian:</span>
                <span class="fw-bold text-success" id="changeDisplay">Rp 0</span>
            </div>
        `;

        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn text-white" style="background-color: #50200C;" onclick="processPayment()">Proses Bayar</button>
        `;
    }

    function formatRupiah(number) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number);
    }

    // ============================================
    // INITIALIZATION & EVENT LISTENERS
    // ============================================

    // 1. Search Listener
    const searchInput = document.getElementById("searchInput");
    if (searchInput) searchInput.addEventListener("keyup", window.filterMenu);

    // 2. Modal Reset Listener
    const paymentModal = document.getElementById("paymentModal");
    if (paymentModal) {
        paymentModal.addEventListener("hidden.bs.modal", function () {
            resetModalUI();
        });
    }

    // 3. [FIXED] CATEGORY BUTTONS LISTENER (Menghidupkan Tombol Kategori)
    const categoryButtons = document.querySelectorAll(".category-filter-btn");
    if (categoryButtons) {
        categoryButtons.forEach((btn) => {
            btn.addEventListener("click", function () {
                // Ambil data kategori dari tombol yang diklik
                const category = this.getAttribute("data-category");
                // Panggil fungsi setCategory
                window.setCategory(category, this);
            });
        });
    }
})();

/**
 * Recipe Management JavaScript
 * File: resources/js/pages/recipe.js
 */

(function () {
    "use strict";

    console.log("🍳 Recipe Editor Loaded - Fixed & Optimized!");

    let currentRecipe = [];

    // Cek apakah kita berada di halaman resep
    // Kita cek container utama atau salah satu elemen unik
    const addBtn = document.getElementById("add-button");
    if (!addBtn) return; // Stop jika bukan di halaman resep

    // ============================================
    // 1. FORM TAMBAH MENU BARU
    // ============================================
    const formTambahMenu = document.getElementById("formTambahMenu");
    if (formTambahMenu) {
        formTambahMenu.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Simpan teks asli
            const originalText = submitBtn.innerHTML;

            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

            fetch(window.recipeConfig.createMenuRoute, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": window.recipeConfig.csrfToken,
                },
                body: formData,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.errors) {
                    // Handle Error Validasi Laravel
                    let errorMsg = "";
                    for (const [key, value] of Object.entries(data.errors)) {
                        errorMsg += `• ${value}<br>`;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: errorMsg,
                        confirmButtonColor: '#50200C'
                    });
                } else if (data.success || data.status === 'success') {
                    // Sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Menu berhasil ditambahkan.',
                        confirmButtonColor: '#50200C',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                    
                    // Tutup modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById("modalTambahMenu"));
                    if (modal) modal.hide();
                } else {
                    throw new Error(data.message || "Gagal menyimpan data.");
                }
            })
            .catch((error) => {
                console.error("❌ Error:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan: ' + error.message,
                    confirmButtonColor: '#50200C'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // ============================================
    // 2. LOAD RECIPE DATA (ATUR RESEP) - PAKAI DELEGATION
    // ============================================
    // Menggunakan document.addEventListener agar tombol dinamis tetap terbaca
    document.addEventListener('click', function(e) {
        // Cek apakah yang diklik adalah tombol .load-recipe-btn atau anaknya
        const target = e.target.closest('.load-recipe-btn');
        
        if (target) {
            e.preventDefault();
            const menuId = target.getAttribute("data-menu-id");
            const menuName = target.getAttribute("data-menu-name");

            console.log("📖 Loading recipe for menu:", menuId, menuName);

            // Update UI Kolom Kanan
            document.getElementById("editingMenuName").innerHTML = `Mengedit resep untuk: <strong style="color: #50200C;">${menuName}</strong>`;
            document.getElementById("editingMenuId").value = menuId;
            document.getElementById("btnSaveRecipe").disabled = false;
            document.getElementById("selectedIngredientsList").innerHTML =
                '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><br><small>Memuat data...</small></div>';

            currentRecipe = [];

            // Fetch Data
            fetch(`/recipes/get/${menuId}`)
                .then((response) => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then((data) => {
                    console.log("📦 Recipe data received:", data);
                    if (data.length > 0) {
                        data.forEach((item) => {
                            currentRecipe.push({
                                id: item.ingredient_id,
                                name: item.name,
                                unit: item.unit,
                                qty: parseFloat(item.quantity),
                            });
                        });
                    }
                    renderRecipeList();
                })
                .catch((error) => {
                    console.error("❌ Error loading recipe:", error);
                    document.getElementById("selectedIngredientsList").innerHTML =
                        `<div class="text-danger text-center p-3">Gagal memuat resep.<br>Error: ${error.message}</div>`;
                });
        }
    });

    // ============================================
    // 3. ADD INGREDIENT TO LIST (Button +)
    // ============================================
    document.querySelectorAll(".available-ingredient-item").forEach((item) => {
        item.addEventListener("click", function () {
            const menuId = document.getElementById("editingMenuId").value;
            
            if (!menuId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Menu Dulu',
                    text: 'Silakan klik tombol "Atur Resep" pada salah satu menu di sebelah kiri.',
                    confirmButtonColor: '#50200C',
                    customClass: {
                        title: "text-50200C",
                        htmlContainer: "text-50200C"
                    }
                });
                return;
            }

            const ingId = this.getAttribute("data-ing-id");
            // Cek duplikasi
            const existing = currentRecipe.find((r) => r.id == ingId);

            if (existing) {
                // Highlight item yang sudah ada (Visual feedback)
                const existingRow = document.querySelector(`.ingredient-item[data-ing-id="${ingId}"]`);
                if(existingRow) {
                    existingRow.style.border = "2px solid #50200C";
                    setTimeout(() => existingRow.style.border = "1px solid #e0e0e0", 500);
                }
                return; // Jangan tambah lagi
            }

            console.log("➕ Adding ingredient:", ingId);

            currentRecipe.push({
                id: ingId,
                name: this.getAttribute("data-ing-name"),
                unit: this.getAttribute("data-ing-unit"),
                qty: 1, // Default qty
            });

            renderRecipeList();
        });
    });

    // ============================================
    // 4. RENDER RECIPE LIST (Logic Tampilan)
    // ============================================
    function renderRecipeList() {
        const container = document.getElementById("selectedIngredientsList");
        const countSpan = document.getElementById("selectedCount");
        const template = document.getElementById("ingredientRowTemplate");

        if (!container || !countSpan || !template) return;

        container.innerHTML = "";
        countSpan.textContent = currentRecipe.length;

        if (currentRecipe.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted mt-4">
                    <i class="fas fa-carrot fa-3x mb-3" style="opacity: 0.3; color: #50200C"></i>
                    <p style="color: #50200C">Belum ada bahan baku ditambahkan.</p>
                    <small style="color: #50200C">Cari dan klik bahan di bawah untuk menambahkan.</small>
                </div>`;
            return;
        }

        currentRecipe.forEach((item, index) => {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector(".ingredient-item");

            // Set Data
            row.setAttribute("data-ing-id", item.id);
            row.querySelector(".ingredient-name").textContent = item.name;
            row.querySelector(".ingredient-unit").textContent = `Satuan: ${item.unit}`;

            const qtyInput = row.querySelector(".qty-input");
            qtyInput.value = item.qty;

            // Event: Ganti Jumlah
            qtyInput.addEventListener("change", function () {
                const newQty = parseFloat(this.value);
                if (newQty > 0) {
                    currentRecipe[index].qty = newQty;
                } else {
                    this.value = currentRecipe[index].qty; // Reset kalau user input <= 0
                }
            });

            // Event: Hapus Bahan
            row.querySelector(".btn-remove-ing").addEventListener("click", function () {
                currentRecipe.splice(index, 1);
                renderRecipeList();
            });

            container.appendChild(clone);
        });
    }

    // ============================================
    // 5. SAVE RECIPE (Simpan Kanan)
    // ============================================
    const btnSaveRecipe = document.getElementById("btnSaveRecipe");
    if (btnSaveRecipe) {
        btnSaveRecipe.addEventListener("click", function () {
            const menuId = document.getElementById("editingMenuId").value;

            if (!menuId || currentRecipe.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Pastikan menu dipilih dan minimal ada 1 bahan baku.',
                    confirmButtonColor: '#50200C',
                    customClass: {
                        title: "text-50200C",
                        htmlContainer: "text-50200C"
                    }
                });
                return;
            }

            // Loading State
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

            const payload = {
                menu_id: menuId,
                ingredients: currentRecipe.map((item) => ({
                    ingredient_id: item.id,
                    quantity: item.qty,
                })),
            };

            fetch(window.recipeConfig.updateRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.recipeConfig.csrfToken,
                },
                body: JSON.stringify(payload),
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.status === "success" || data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: 'Resep menu berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false,
                        confirmButtonColor: '#50200C'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || "Gagal menyimpan.");
                }
            })
            .catch((error) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message,
                    confirmButtonColor: '#50200C'
                });
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    }

    // ============================================
    // 6. SEARCH MENU (Pencarian Menu)
    // ============================================
    const searchMenu = document.getElementById("searchMenu");
    if (searchMenu) {
        searchMenu.addEventListener("keyup", function () {
            const value = this.value.toLowerCase();
            const cards = document.querySelectorAll(".menu-item-card");
            
            cards.forEach((card) => {
                const name = card.getAttribute("data-name");
                if (name.includes(value)) {
                    // Tampilkan (hapus display: none inline style jika ada)
                    card.style.display = ""; 
                    // Pastikan class bootstrap col tetap bekerja
                    card.classList.remove("d-none");
                } else {
                    // Sembunyikan
                    card.style.display = "none";
                }
            });
        });
    }

    // ============================================
    // 7. SEARCH INGREDIENT (Pencarian Bahan)
    // ============================================
    const searchIngredient = document.getElementById("searchIngredient");
    if (searchIngredient) {
        searchIngredient.addEventListener("keyup", function () {
            const value = this.value.toLowerCase();
            document.querySelectorAll(".available-ingredient-item").forEach((item) => {
                const name = item.getAttribute("data-ing-name").toLowerCase();
                // Toggle visibility menggunakan d-flex / d-none
                if (name.includes(value)) {
                    item.classList.remove("d-none");
                    item.classList.add("d-flex");
                } else {
                    item.classList.remove("d-flex");
                    item.classList.add("d-none");
                }
            });
        });
    }

    // ============================================
    // 8. DELETE MENU (SWEETALERT2) - FIXED
    // ============================================
    // Menggunakan Delegation juga untuk tombol delete di dalam dropdown
    document.addEventListener('click', function(e) {
        // Cari tombol delete menu terdekat
        const deleteBtn = e.target.closest('.btn-delete-menu');
        
        if (deleteBtn) {
            e.preventDefault();
            const form = deleteBtn.closest("form");
            const menuName = deleteBtn.getAttribute("data-name");

            Swal.fire({
                title: "Hapus Menu?",
                html: `Yakin ingin menghapus <b>${menuName}</b>?<br><small style="color: #A94442">Semua resep terkait juga akan dihapus permanen!</small>`,
                icon: "warning",
                background: '#F7F3E4',
                showCancelButton: true,
                confirmButtonColor: "#F2C2B8",
                cancelButtonColor: "#8FB8E1",
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal',
                iconColor: '#50200C',
                customClass: {
                    confirmButton: "text-50200C",
                    cancelButton: "text-50200C",
                    title: "text-50200C",
                    htmlContainer: "text-50200C"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    });

})();
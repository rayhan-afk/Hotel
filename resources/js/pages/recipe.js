/**
 * Recipe Management JavaScript
 * File: resources/js/pages/recipe.js
 */

(function () {
    "use strict";

    console.log("🍳 Recipe Editor Loaded!");

    let currentRecipe = [];

    // Check if we're on recipe page
    const recipeContainer = document.getElementById("modalTambahMenu");
    if (!recipeContainer) return;

   // ============================================
    // 1. FORM TAMBAH MENU BARU (SUDAH DIPERBAIKI)
    // ============================================
    const formTambahMenu = document.getElementById("formTambahMenu");
    if (formTambahMenu) {
        formTambahMenu.addEventListener("submit", function (e) {
            e.preventDefault();

            // 1. Ambil data form
            const formData = new FormData(this);
            
            // CEK DEBUG: Lihat di Console browser (F12) apakah file terdeteksi?
            const fileGambar = formData.get('image');
            console.log("📸 File yang dikirim:", fileGambar); 

            const submitBtn = this.querySelector('button[type="submit"]');

            // 2. Ubah tombol jadi loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

            fetch(window.recipeConfig.createMenuRoute, {
                method: "POST",
                headers: {
                    // WAJIB ADA: Agar Laravel merespon dengan JSON jika ada error validasi
                    "Accept": "application/json", 
                    "X-CSRF-TOKEN": window.recipeConfig.csrfToken,
                },
                body: formData, // Fetch otomatis mengatur Content-Type untuk FormData
            })
                .then((response) => response.json())
                .then((data) => {
                    // Tangani jika validasi Laravel gagal (Error 422)
                    if (data.errors) {
                         let errorMsg = '';
                         for (const [key, value] of Object.entries(data.errors)) {
                             errorMsg += `• ${value}<br>`;
                         }
                         toastr.error(errorMsg, "Gagal Validasi");
                         throw new Error("Validasi Gagal"); // Stop proses
                    }

                    if (data.success || data.message === "Menu berhasil ditambahkan") { // Sesuaikan dengan response controller kamu
                        toastr.success("Menu berhasil ditambahkan! Sekarang Anda bisa atur resepnya.");

                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById("modalTambahMenu"));
                        if (modal) modal.hide();

                        // Reload
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        // Jika success: false
                        toastr.error("Gagal: " + (data.message || "Terjadi kesalahan server"));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan & Atur Resep';
                    }
                })
                .catch((error) => {
                    if(error.message !== "Validasi Gagal") {
                        console.error("❌ Error:", error);
                        toastr.error("Terjadi kesalahan sistem / File terlalu besar (Max 2MB).");
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Simpan & Atur Resep';
                });
        });
    }

    // ============================================
    // 2. LOAD RECIPE DATA
    // ============================================
    document.querySelectorAll(".load-recipe-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const menuId = this.getAttribute("data-menu-id");
            const menuName = this.getAttribute("data-menu-name");

            console.log("📖 Loading recipe for menu:", menuId, menuName);

            document.getElementById(
                "editingMenuName"
            ).innerHTML = `Mengedit resep untuk: <strong style="color: #50200C;">${menuName}</strong>`;
            document.getElementById("editingMenuId").value = menuId;
            document.getElementById("btnSaveRecipe").disabled = false;
            document.getElementById("selectedIngredientsList").innerHTML =
                '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';

            currentRecipe = [];

            // Fetch recipe data
            fetch(`/recipes/get/${menuId}`)
                .then((response) => {
                    console.log("📥 Response status:", response.status);
                    if (!response.ok) {
                        throw new Error(
                            `HTTP error! status: ${response.status}`
                        );
                    }
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
                        console.log("✅ Recipe loaded:", currentRecipe);
                    } else {
                        console.log("ℹ️ No recipe found for this menu");
                    }
                    renderRecipeList();
                })
                .catch((error) => {
                    console.error("❌ Error loading recipe:", error);
                    document.getElementById(
                        "selectedIngredientsList"
                    ).innerHTML =
                        '<div class="text-danger text-center p-3">Gagal memuat resep: ' +
                        error.message +
                        "</div>";
                });
        });
    });

    // ============================================
    // 3. ADD INGREDIENT FROM LIST
    // ============================================
    document.querySelectorAll(".available-ingredient-item").forEach((item) => {
        item.addEventListener("click", function () {
            const menuId = document.getElementById("editingMenuId").value;
            if (!menuId) {
                toastr.warning(
                    "Silakan pilih menu di kolom kiri terlebih dahulu!"
                );
                return;
            }

            const ingId = this.getAttribute("data-ing-id");
            const existing = currentRecipe.find((r) => r.id == ingId);

            if (existing) {
                toastr.info("Bahan ini sudah ada di resep.");
                return;
            }

            console.log("➕ Adding ingredient:", ingId);

            currentRecipe.push({
                id: ingId,
                name: this.getAttribute("data-ing-name"),
                unit: this.getAttribute("data-ing-unit"),
                qty: 1,
            });

            renderRecipeList();
        });
    });

    // ============================================
    // 4. RENDER RECIPE LIST
    // ============================================
    function renderRecipeList() {
        console.log("🔄 Rendering recipe list...");

        const container = document.getElementById("selectedIngredientsList");
        const countSpan = document.getElementById("selectedCount");
        const template = document.getElementById("ingredientRowTemplate");

        if (!container || !countSpan || !template) {
            console.error("❌ Required elements not found");
            return;
        }

        container.innerHTML = "";
        countSpan.textContent = currentRecipe.length;

        if (currentRecipe.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted mt-4">
                    <i class="fas fa-carrot fa-3x mb-3" style="opacity: 0.3"></i>
                    <p>Belum ada bahan baku ditambahkan.</p>
                    <small>Cari dan klik bahan di bawah untuk menambahkan.</small>
                </div>`;
            console.log("ℹ️ No ingredients in recipe");
            return;
        }

        currentRecipe.forEach((item, index) => {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector(".ingredient-item");

            row.setAttribute("data-ing-id", item.id);
            row.querySelector(".ingredient-name").textContent = item.name;
            row.querySelector(
                ".ingredient-unit"
            ).textContent = `Satuan: ${item.unit}`;

            const qtyInput = row.querySelector(".qty-input");
            qtyInput.value = item.qty;

            qtyInput.addEventListener("change", function () {
                const newQty = parseFloat(this.value);
                if (newQty > 0) {
                    currentRecipe[index].qty = newQty;
                    console.log(`📊 Updated qty for ${item.name}: ${newQty}`);
                }
            });

            row.querySelector(".btn-remove-ing").addEventListener(
                "click",
                function () {
                    console.log(`🗑️ Removing ingredient: ${item.name}`);
                    currentRecipe.splice(index, 1);
                    renderRecipeList();
                }
            );

            container.appendChild(clone);
        });

        console.log("✅ Rendered", currentRecipe.length, "ingredients");
    }

    // ============================================
    // 5. SAVE RECIPE
    // ============================================
    const btnSaveRecipe = document.getElementById("btnSaveRecipe");
    if (btnSaveRecipe) {
        btnSaveRecipe.addEventListener("click", function () {
            const menuId = document.getElementById("editingMenuId").value;

            if (!menuId || currentRecipe.length === 0) {
                toastr.warning(
                    "Data tidak lengkap. Pastikan menu dipilih dan minimal ada 1 bahan."
                );
                return;
            }

            console.log("💾 Saving recipe for menu:", menuId);
            console.log("📦 Data to save:", currentRecipe);

            this.disabled = true;
            this.innerHTML =
                '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';

            const payload = {
                menu_id: menuId,
                ingredients: currentRecipe.map((item) => ({
                    ingredient_id: item.id,
                    quantity: item.qty,
                })),
            };

            console.log("📤 Payload:", payload);

            fetch(window.recipeConfig.updateRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": window.recipeConfig.csrfToken,
                },
                body: JSON.stringify(payload),
            })
                .then((response) => {
                    console.log("📥 Save response status:", response.status);
                    return response.json();
                })
                .then((data) => {
                    console.log("📥 Save response:", data);

                    if (data.status === "success") {
                        toastr.success("Resep berhasil disimpan!");
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error("Gagal menyimpan: " + data.message);
                        this.disabled = false;
                        this.innerHTML =
                            '<i class="fas fa-save me-2"></i> Simpan Resep Menu Ini';
                    }
                })
                .catch((error) => {
                    console.error("❌ Error saving recipe:", error);
                    toastr.error("Terjadi kesalahan sistem: " + error.message);
                    this.disabled = false;
                    this.innerHTML =
                        '<i class="fas fa-save me-2"></i> Simpan Resep Menu Ini';
                });
        });
    }

    // ============================================
    // 6. RESET FORM
    // ============================================
    window.resetForm = function () {
        console.log("🔄 Resetting form...");

        document.getElementById("editingMenuName").innerHTML =
            '<span class="text-primary">Mode Tambah Baru: Silakan pilih menu di samping dulu.</span>';
        document.getElementById("editingMenuId").value = "";
        currentRecipe = [];
        renderRecipeList();
        document.getElementById("btnSaveRecipe").disabled = true;
    };

    // ============================================
    // 7. SEARCH FEATURES
    // ============================================
    const searchMenu = document.getElementById("searchMenu");
    if (searchMenu) {
        searchMenu.addEventListener("keyup", function () {
            const value = this.value.toLowerCase();
            document.querySelectorAll(".menu-item-card").forEach((card) => {
                const name = card.getAttribute("data-name");
                card.style.display = name.includes(value) ? "block" : "none";
            });
        });
    }

    const searchIngredient = document.getElementById("searchIngredient");
    if (searchIngredient) {
        searchIngredient.addEventListener("keyup", function () {
            const value = this.value.toLowerCase();
            document
                .querySelectorAll(".available-ingredient-item")
                .forEach((item) => {
                    const name = item
                        .getAttribute("data-ing-name")
                        .toLowerCase();
                    item.style.display = name.includes(value) ? "flex" : "none";
                });
        });
    }

    console.log("✅ Recipe Editor initialized successfully!");
})();

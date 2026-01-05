@extends('template.master')
@section('title', 'Atur Resep Menu')

@section('content')
{{-- CDN SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- CSS Tambahan --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* --- PERBAIKAN UTAMA DI SINI --- */
    .product-card { 
        border: 1px solid #e0e0e0; 
        border-radius: 12px; 
        background: #fff; 
        transition: all 0.2s; 
        cursor: default;
        width: 100%;
 
        min-height: 250px; 
        position: relative;
        
        /* GANTI DARI HIDDEN JADI VISIBLE */
        /* Ini kuncinya: agar dropdown bisa 'tumpah' keluar dari kotak kartu */
        overflow: visible; 
        z-index: 1;

        display: flex;
        flex-direction: column;
    }

    .product-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 5px 15px rgba(80, 32, 12, 0.1); 
        border-color: #50200C; 
        
        /* Pastikan saat di-hover, kartu ini posisinya lebih tinggi dari tetangganya */
        z-index: 10; 
    }

    /* Style Tombol Titik Tiga */
    .card-actions { 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        z-index: 20; /* Lebih tinggi dari konten card */
    }

    .btn-action-menu { 
        background: rgba(255, 255, 255, 0.9); 
        border-radius: 50%; 
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #50200C; 
        border: 1px solid #e0e0e0; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.2s;
    }
    .btn-action-menu:hover { 
        background: #50200C; 
        color: #fff; 
        border-color: #50200C;
    }

    /* --- PERBAIKAN AGAR MENU PROPOSIONAL --- */
    .dropdown-menu {
        min-width: 200px; /* Lebarkan menu agar teks tidak kejepit */
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15); /* Bayangan lebih tegas */
        border-radius: 10px;
        padding: 8px 0;
        margin-top: 5px !important;
        z-index: 1000; /* Pastikan muncul paling depan */
    }

    .dropdown-item {
        padding: 8px 20px;
        font-size: 0.9rem;
        color: #333;
        transition: all 0.2s;
    }

    .dropdown-item:hover {
        background-color: #F7F3E4;
        color: #50200C;
    }

    .dropdown-item i {
        width: 20px; /* Rapikan icon agar sejajar */
        text-align: center;
    }

    /* Style untuk kolom kanan (Editor Resep) - Tetap sama */
    .recipe-editor-container { background-color: #FDFBF7; border-left: 1px solid #e0e0e0; height: calc(100vh - 100px); display: flex; flex-direction: column; }
    .ingredient-list-scroll { flex: 1; overflow-y: auto; padding: 1rem; }
    .ingredient-item { background: #F7F3E4; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 0.5rem; padding: 0.5rem; display: flex; align-items: center; justify-content: space-between; }
    .ingredient-search-box { background: #F7F3E4; color: #50200C; padding: 1rem; border-top: 1px solid #e0e0e0;border-bottom: 1px solid #e0e0e0; }
    .available-ingredient-item { cursor: pointer; transition: all 0.2s; border: 1px solid transparent;}
    .available-ingredient-item:hover { background-color: #F7F3E4; border-color: #50200C; }
    .qty-input { width: 70px; text-align: center; border: 1px solid #50200C; color: #50200C; border-radius: 4px; }
</style>

<div class="container-fluid p-0">
    <div class="row g-0 h-100">
        {{-- ============================================== --}}
        {{-- KOLOM KIRI: DAFTAR MENU --}}
        {{-- ============================================== --}}
        <div class="col-md-8 p-4" style="background: #F7F3E4; min-height: 100vh;">
            
            {{-- ALERT MESSAGES --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-left: 5px solid #198754;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-left: 5px solid #dc3545;">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <button id="add-button" type="button" class="add-room-btn" data-bs-toggle="modal" data-bs-target="#modalTambahMenu">
                    <i class="fas fa-plus"></i>
                    Tambah Menu Baru
                </button>

                <div class="stock-status-compact d-flex align-items-center" 
                     style="background-color: #fff; border: 1px solid #e0e0e0; padding: 8px 15px; border-radius: 12px; gap: 10px;">
                    <span class="badge" style="background-color: #FAE8A4; color: #50200C !important;">INFO</span>
                    <span style="color: #50200C; font-weight: 600; font-size: 0.9rem;">Mode Pengaturan Resep</span>
                </div>
            </div>

            <div class="table-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 style="color: #50200C;"><i class="fas fa-book-open me-2"></i>Daftar Menu Restoran</h4>
                    <p class="mb-0" style="color: #50200C">Klik "Edit Resep" untuk bahan baku, atau tombol menu (⋮) untuk edit info/hapus.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm shadow-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search" style="color: #50200C"></i></span>
                        <input type="text" id="searchMenu" class="form-control border-start-0" style="color: #50200C" placeholder="Cari menu...">
                    </div>
                </div>
            </div>

            <div class="row g-3" style="max-height: 70vh; overflow-y: auto; padding-right: 5px;">
                @forelse($menus as $menu)
                <div class="col-xl-3 col-lg-4 col-md-6 menu-item-card" data-name="{{ strtolower($menu->name) }}">
                    <div class="product-card py-2">
                        
                        {{-- BUTTON ACTION (TITIK TIGA) --}}
                        <div class="dropdown card-actions">
                            <button class="btn btn-sm btn-action-menu" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <h6 class="dropdown-header" style="color: #50200C">Aksi Menu</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('recipes.editMenu', $menu->id) }}" style="color: #50200C">
                                        <i class="fas fa-edit me-2" style="color: #50200C"></i>Edit Info Menu
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('recipes.destroyMenu', $menu->id) }}" method="POST" class="d-inline form-delete-menu">
                                        @csrf
                                        @method('DELETE')
                                        
                                        <button type="button" class="dropdown-item text-danger btn-delete-menu" data-name="{{ $menu->name }}">
                                            <i class="fas fa-trash-alt me-2"></i>Hapus Menu
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                        <div class="px-3 pt-2 d-flex align-items-center">
                             @if($menu->image)
                                <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #F7F3E4;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 2px solid #e0e0e0;">
                                    <i class="fas fa-utensils" style="color: #50200C"></i>
                                </div>
                            @endif
                            <div class="ms-3 flex-grow-1" style="padding-right: 20px;">
                                <h6 class="fw-bold mb-0 text-truncate" style="color: #50200C; max-width: 110px;">{{ $menu->name }}</h6>
                                <small class="" style="color: #50200C">{{ $menu->category }}</small>
                            </div>
                        </div>
                        
                        <hr class="my-2">

                        <div class="px-3 pb-2 d-flex justify-content-between align-items-center">
                            @if($menu->ingredients_count > 0)
                                <span class="badge badge-approved" style="font-size: 0.7rem;"><i class="fas fa-check me-1"></i>Sudah Ada</span>
                            @else
                                <span class="badge badge-rejected" style="font-size: 0.7rem;"><i class="fas fa-times me-1"></i>Belum Ada</span>
                            @endif
                        </div>

                        <div class="px-3 pb-3 mt-auto">
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary rounded-pill px-2 w-100 load-recipe-btn"
                                data-menu-id="{{ $menu->id }}"
                                data-menu-name="{{ addslashes($menu->name) }}">
                                <i class="fas fa-scroll me-1"></i> Atur Resep
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5"><h5 class="" style="color: #50200C">Belum ada data menu.</h5></div>
                @endforelse
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- KOLOM KANAN: EDITOR RESEP --}}
        {{-- ============================================== --}}
        <div class="col-md-4 p-0">
            <div class="recipe-editor-container" style="background: #F7F3E4;">
                <div class="p-4 border-bottom">
                    <h5 class="mb-1 fw-bold" style="color: #50200C;">
                        <i class="fas fa-scroll me-2"></i>Editor Resep
                    </h5>
                    <h6 class="mb-0" id="editingMenuName" style="color: #50200C">Silakan pilih "Atur Resep" di bawah</h6>
                    <input type="hidden" id="editingMenuId" value="">
                </div>

                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold small" style="color: #50200C">Bahan Baku Terpilih (<span id="selectedCount">0</span>)</span>
                    <small class="" style="color: #50200C">Atur takaran per porsi</small>
                </div>
                
                <div id="selectedIngredientsList" class="ingredient-list-scroll" style="max-height: 300px;">
                    <div class="text-center mt-4" style="color: #50200C">
                        <i class="fas fa-carrot fa-3x mb-3"></i>
                        <p>Belum ada bahan baku ditambahkan.</p>
                        <small>Cari dan klik bahan di bawah untuk menambahkan.</small>
                    </div>
                </div>

                <div class="ingredient-search-box">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white"><i class="fas fa-search" style="color: #50200C"></i></span>
                        <input type="text" id="searchIngredient" class="form-control" placeholder="Cari bahan baku...">
                    </div>
                    <div style="color: #50200C; font-size: 0.8rem" class="mb-2">Klik bahan untuk menambahkan ke atas.</div>
                    
                    <div class="list-group overflow-auto" style="max-height: 200px;" id="availableIngredientsList">
                        @foreach($ingredients as $ing)
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center available-ingredient-item"
                             style="background-color: #F7F3E4"
                             data-ing-id="{{ $ing->id }}"
                             data-ing-name="{{ $ing->name }}"
                             data-ing-unit="{{ $ing->unit }}"
                             data-ing-stock="{{ $ing->stock }}">
                            <div>
                                <span class="fw-bold" style="color: #50200C;">{{ $ing->name }}</span>
                                <br>
                                <small class="" style="color: #50200C">Stok: {{ $ing->stock }} {{ $ing->unit }}</small>
                            </div>
                            <i class="fas fa-plus-circle" style="color: #50200C"></i>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-3 bg-white mt-auto border-top">
                    <button class="btn w-100 py-3 fs-6 text-white fw-bold" id="btnSaveRecipe" style="background-color: #50200C;" disabled>
                        <i class="fas fa-save me-2"></i> Simpan Resep Menu Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TEMPLATE JAVASCRIPT --}}
<template id="ingredientRowTemplate">
    <div class="ingredient-item shadow-sm" data-ing-id="">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="me-3 text-center" style="width: 40px;">
                <i class="fas fa-box" style="color: #50200C"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold ingredient-name" style="color: #50200C;">Nama Bahan</h6>
                <small class="ingredient-unit" style="color: #50200C">Satuan: Gram</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="number" class="form-control form-control-sm qty-input" value="1" min="0.01" step="0.01" placeholder="Jml">
            <button class="btn btn-sm btn-outline-danger btn-remove-ing">
                <i class="fas fa-trash-alt" style="color: #50200C"></i>
            </button>
        </div>
    </div>
</template>

{{-- MODAL TAMBAH MENU BARU --}}
<div class="modal fade" id="modalTambahMenu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #F7F3E4;">
                <h5 class="modal-title fw-bold" style="color: #50200C;">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Menu Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambahMenu" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="background-color: #F7F3E4;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Menu <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Contoh: Nasi Goreng Spesial">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="category" id="category" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <option value="Food">Makanan (Food)</option>                                    
                                    <option value="Beverage">Minuman (Beverage)</option>                                   
                                    <option value="Snack">Camilan (Snack)</option>                                   
                                    <option value="Other">Lainnya (Other)</option>
                                </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Harga <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required placeholder="25000" min="0" step="1000">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Opsional: Deskripsi menu..."></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Gambar Menu</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="" style="color: #50200C">Format: JPG, PNG. Max 2MB</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #F7F3E4;">
                    <button type="button" class="btn btn-modal-close" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="fas fa-save me-2"></i>Simpan & Atur Resep
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- CONFIG FOR EXTERNAL JS --}}
<script>
    // Config untuk file JS eksternal
    window.recipeConfig = {
        createMenuRoute: "{{ route('recipes.createMenu') }}",
        updateRoute: "{{ route('recipes.updateApi') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>

<script src="{{ asset('js/recipes.js') }}"></script>
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Menu;

class RecipeController extends Controller
{
    // 1. Tampilkan Halaman Utama
    public function index()
    {
        // Tambahkan ->where('is_available', 1) agar menu yg "dihapus" tidak tampil
        $menus = Menu::where('is_available', 1)
                     ->withCount('ingredients')
                     ->get();
                     
        $ingredients = DB::table('ingredients')->orderBy('name', 'asc')->get();

        return view('recipes.index', compact('menus', 'ingredients'));
    }

    // 2. API: Ambil Detail Resep (Mengembalikan JSON Flat)
    public function getRecipe($menuId)
    {
        $recipe = DB::table('menu_ingredients')
            ->join('ingredients', 'menu_ingredients.ingredient_id', '=', 'ingredients.id')
            ->where('menu_ingredients.menu_id', $menuId)
            ->select(
                'menu_ingredients.ingredient_id', 
                'menu_ingredients.quantity_needed as quantity', // Sesuai DB kamu
                'ingredients.name', // JS mengakses ini sebagai: item.name
                'ingredients.unit'  // JS mengakses ini sebagai: item.unit
            )
            ->get();

        return response()->json($recipe);
    }

    // 3. API: Simpan Resep
    public function updateApi(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'ingredients' => 'required|array',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // A. Hapus Resep Lama
            DB::table('menu_ingredients')->where('menu_id', $request->menu_id)->delete();

            // B. Masukkan Resep Baru
            $insertData = [];
            foreach ($request->ingredients as $item) {
                $insertData[] = [
                    'menu_id'       => $request->menu_id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity_needed' => $item['quantity'], // Memasukkan ke kolom quantity_needed
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (count($insertData) > 0) {
                DB::table('menu_ingredients')->insert($insertData);
            }

            DB::commit();
            
            return response()->json(['status' => 'success', 'message' => 'Resep berhasil disimpan']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. ✨ NEW: API Tambah Menu Baru
    public function createMenu(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:Food,Beverage,Snack,Other',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        try {
            DB::beginTransaction();

            $menu = new Menu();
            $menu->name = $request->name;
            $menu->category = $request->category;
            $menu->price = $request->price;
            $menu->description = $request->description;
            
            // Set is_available ke 1 (tersedia) secara default
            $menu->is_available = 1;

            // Handle Upload Gambar
            if ($request->hasFile('image')) {
                // Simpan ke storage/app/public/menus
                $imagePath = $request->file('image')->store('menus', 'public');
                $menu->image = $imagePath;
            }

            $menu->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil ditambahkan',
                'menu_id' => $menu->id,
                'menu' => $menu
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Hapus gambar jika upload berhasil tapi save gagal
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan menu: ' . $e->getMessage()
            ], 500);
        }
    }

    // 5. ✨ NEW: API Hapus Menu (Opsional, untuk fitur delete nanti)
    public function deleteMenu($menuId)
    {
        try {
            DB::beginTransaction();

            $menu = Menu::findOrFail($menuId);

            // Hapus gambar dari storage jika ada
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }

            // Hapus resep terkait
            DB::table('menu_ingredients')->where('menu_id', $menuId)->delete();

            // Hapus menu
            $menu->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Menu berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus menu: ' . $e->getMessage()
            ], 500);
        }
    }
    // =========================================================================
    // BAGIAN 3: WEB CRUD (FITUR BARU UNTUK EDIT & HAPUS VIA HALAMAN WEB)
    // =========================================================================

    /**
     * WEB: Tampilkan Form Edit Menu
     * Route: GET /recipes/edit-menu/{id}
     */
    public function editMenu($id)
    {
        $menu = Menu::findOrFail($id);
        
        // Kategori hardcode sesuai dengan database/enum kamu
        $categories = ['Food', 'Beverage', 'Snack', 'Other']; 

        // Jika nanti mau buat view terpisah, pastikan file ini ada
        return view('recipes.edit_menu', compact('menu', 'categories'));
    }

    /**
     * WEB: Proses Update Data Menu
     * Route: PUT /recipes/update-menu/{id}
     */
    public function updateMenu(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:Food,Beverage,Snack,Other',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $menu = Menu::findOrFail($id);
            
            // Update Data Text
            $menu->name        = $request->name;
            $menu->category    = $request->category;
            $menu->price       = $request->price;
            $menu->description = $request->description;

            // Handle Image Upload (Hapus lama, Simpan baru)
            if ($request->hasFile('image')) {
                // 1. Hapus gambar lama jika ada
                if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                    Storage::disk('public')->delete($menu->image);
                }

                // 2. Upload gambar baru
                $imagePath = $request->file('image')->store('menus', 'public');
                $menu->image = $imagePath;
            }

            $menu->save();

            DB::commit();

            return redirect()->route('recipes.index')->with('success', 'Menu berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal update menu: ' . $e->getMessage());
        }
    }

    /**
     * WEB: Proses Hapus Menu (Redirect version)
     * Route: DELETE /recipes/delete-menu/{id}
     */
   public function destroyMenu($id)
    {
        try {
            DB::beginTransaction();

            $menu = Menu::findOrFail($id);

            // 1. CEK RIWAYAT TRANSAKSI (Sesuai nama tabel di error kamu: transaction_pos_details)
            // Kita cek apakah menu_id ini ada di tabel detail transaksi
            $isUsedInTransaction = DB::table('transaction_pos_details')
                                     ->where('menu_id', $id)
                                     ->exists();

            if ($isUsedInTransaction) {
                // KASUS A: Menu sudah pernah terjual (PENTING: Jangan Hapus Permanen!)
                
                // Cukup set jadi tidak tersedia (Soft Delete Manual)
                $menu->is_available = 0; 
                
                // Opsional: Kosongkan gambar biar hemat storage (kalau mau)
                // if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                //    Storage::disk('public')->delete($menu->image);
                //    $menu->image = null;
                // }
                
                $menu->save();

                DB::commit();

                // Beri pesan sukses tapi informatif
                return redirect()->route('recipes.index')
                    ->with('success', 'Menu sudah ada di riwayat transaksi. Menu tidak dihapus permanen, tetapi DIARSIPKAN (disembunyikan) agar laporan tetap aman.');
            
            } else {
                // KASUS B: Menu belum pernah terjual (Boleh Hapus Permanen)

                // 1. Hapus Gambar
                if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                    Storage::disk('public')->delete($menu->image);
                }

                // 2. Hapus Relasi Ingredient (Resep)
                DB::table('menu_ingredients')->where('menu_id', $id)->delete();

                // 3. Hapus Menu Selamanya
                $menu->delete();

                DB::commit();

                return redirect()->route('recipes.index')->with('success', 'Menu berhasil dihapus permanen.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses menu: ' . $e->getMessage());
        }
    }
}

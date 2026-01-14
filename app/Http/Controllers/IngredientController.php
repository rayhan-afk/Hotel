<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Repositories\Interface\IngredientRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\StockOpname;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // <--- PENTING: Tambahkan ini untuk fitur Transaction

class IngredientController extends Controller
{
    public function __construct(
        private IngredientRepositoryInterface $ingredientRepository
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->ingredientRepository->getIngredientsDatatable($request);
        }

        // 1. Ambil Kategori (Existing code)
        $categories = $this->ingredientRepository->getCategories();
        
        // 2. [PERBAIKAN UTAMA DISINI] 
        // Ambil semua data bahan baku untuk ditampilkan di Modal Stock Opname
        // Kita urutkan A-Z biar enak ngecek fisiknya
        // ✅ PASTIKAN BARIS INI ADA!
         $ingredients = Ingredient::orderBy('name', 'asc')->get();
        
        

    // ✅ DAN BARIS INI JUGA ADA!
    return view('ingredient.index', compact('categories', 'ingredients'));
    }

    public function create()
    {
        $categories = $this->ingredientRepository->getCategories();
        $view = view('ingredient.create', compact('categories'))->render();
        return response()->json(['view' => $view]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        Ingredient::create($validated);

        return response()->json(['message' => 'Bahan baku berhasil ditambahkan!']);
    }

    public function edit(Ingredient $ingredient)
    {
        $categories = $this->ingredientRepository->getCategories();
        $view = view('ingredient.edit', compact('ingredient', 'categories'))->render();
        return response()->json(['view' => $view]);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $ingredient->update($validated);

        return response()->json(['message' => 'Bahan baku berhasil diperbarui!']);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();
        return response()->json(['message' => 'Bahan baku berhasil dihapus!']);
    }

    // --- FITUR STOCK OPNAME ---
    // --- FITUR STOCK OPNAME (VERSI FIX) ---
    public function storeOpname(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:ingredients,id',
            'items.*.physical_stock' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = 0; // Untuk menghitung berapa bahan yang berubah

            foreach ($request->items as $item) {
                // Ambil data bahan terbaru langsung dari Database (Biar Realtime)
                $ingredient = Ingredient::find($item['id']);

                if ($ingredient) {
                    // CASTING: Paksa jadi float biar aman hitungannya
                    $systemStock   = (float) $ingredient->stock; 
                    $physicalStock = (float) $item['physical_stock'];
                    
                    // Hitung selisih
                    $difference = $physicalStock - $systemStock;

                    // Cek selisih dengan toleransi desimal yang sangat kecil
                    // (abs > 0.00001 artinya: jika ada beda sedikit saja, dianggap beda)
                    if (abs($difference) > 0.00001) {
                        
                        // 1. Update Stok Master
                        // Kita pakai method update() langsung biar trigger Eloquent jalan
                        $ingredient->update([
                            'stock' => $physicalStock
                        ]);

                        // 2. Catat di Riwayat Opname
                        StockOpname::create([
                            'ingredient_id'  => $ingredient->id,
                            'system_stock'   => $systemStock, // Stok sebelum berubah
                            'physical_stock' => $physicalStock, // Stok sesudah berubah
                            'difference'     => $difference,
                            'notes'          => $item['notes'] ?? 'Penyesuaian Stock Opname',
                        ]);

                        $updatedCount++;
                    }
                }
            }

            DB::commit();

            // Beri pesan yang spesifik
            if ($updatedCount > 0) {
                return redirect()->back()->with('success', "Berhasil! $updatedCount bahan baku telah disesuaikan stoknya.");
            } else {
                return redirect()->back()->with('warning', 'Tidak ada perubahan stok yang disimpan (Data fisik sama dengan sistem).');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function history()
    {
        // Ambil data riwayat, urutkan dari yang terbaru
        // Kita load relasi 'ingredient' biar nama bahannya muncul
        $histories = \App\Models\StockOpname::with('ingredient')
                        ->orderBy('created_at', 'desc')
                        ->limit(100) // Batasi 100 terakhir biar loading gak berat
                        ->get();

        // Render view khusus tabel history (kita buat setelah ini)
        $view = view('ingredient.history', compact('histories'))->render();

        return response()->json(['view' => $view]);
    }
}
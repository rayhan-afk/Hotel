<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Repositories\Interface\IngredientRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        // --- [PERBAIKAN DISINI] ---
        // Ambil kategori dari Repository (Database)
        // Jangan pakai array manual lagi supaya sinkron dengan data tabel
        $categories = $this->ingredientRepository->getCategories();
        
         

        return view('ingredient.index', compact('categories'));
    }

    public function create()
    {
        // Ambil kategori dari Repository juga
        $categories = $this->ingredientRepository->getCategories();
        
        $view = view('ingredient.create', compact('categories'))->render();
        return response()->json(['view' => $view]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string', // Bisa input kategori baru
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
}
<?php

namespace App\Repositories\Implementation;

use App\Models\Ingredient;
use App\Repositories\Interface\IngredientRepositoryInterface;
use Illuminate\Support\Facades\DB;

class IngredientRepository implements IngredientRepositoryInterface
{
    public function getCategories()
    {
        return [
            'Sayuran',
            'Buah',
            'Daging & Ikan',
            'Bumbu',
            'Sembako',
            'Minuman',
            'Bahan Hewani',
            'Lainnya'
        ];
    }

    public function getIngredientsDatatable($request)
    {
        // Mapping urutan kolom untuk sorting
        $columns = [
            0 => 'name',
            1 => 'category',
            2 => 'stock',
            3 => 'unit',
            4 => 'stock', // Status (disortir by stok)
            5 => 'description',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'stock';
        $dir = $request->input('order.0.dir') ?? 'asc';
        $search = $request->input('search.value');
        $filterCategory = $request->input('category'); 

        $query = Ingredient::query();

        // 1. Filter Kategori
        if (!empty($filterCategory)) {
            $query->where('category', $filterCategory);
        }

        // 2. Pencarian Global
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $totalData = Ingredient::count();
        $totalFiltered = $query->count();

        // === FITUR: PRIORITAS STOK < 5 MUNCUL PALING ATAS ===
        $models = $query
            ->select('*', DB::raw('CASE WHEN stock < 5 THEN 0 ELSE 1 END as stock_priority'))
            ->orderBy('stock_priority', 'asc') // Stok < 5 (priority 0) di atas
            ->orderBy($order, $dir) // Lalu sort sesuai user
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'name' => $model->name,
                'category' => $model->category,
                'stock' => $model->stock,
                'unit' => $model->unit,
                'description' => $model->description ?? '-',
                // Flag untuk warning row (stok < 5)
                'is_low_stock' => $model->stock < 5,
            ];
        }

        return json_encode([
            'draw' => intval($request->input('draw')),
            'iTotalRecords' => $totalData,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData' => $data,
        ]);
    }
}
<?php

namespace App\Repositories\Implementation;

use App\Models\Amenity;
use App\Repositories\Interface\AmenityRepositoryInterface;
use Illuminate\Support\Facades\DB; // <--- WAJIB ADA: Biar DB::raw tidak error

class AmenityRepository implements AmenityRepositoryInterface
{
    public function getAmenities($request)
    {
        return Amenity::orderBy('nama_barang')->get();
    }

    public function getAmenitiesDatatable($request)
    {
        // 1. Mapping Kolom untuk Sorting
        $columns = [
            0 => 'nama_barang',
            1 => 'stok',
            2 => 'satuan',
            3 => 'stok',
            4 => 'keterangan',
        ];

        // 2. Ambil Parameter DataTables dengan nilai default
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIndex = $request->input('order.0.column', 0);
        $order = $columns[$orderIndex] ?? 'stok';
        $dir = $request->input('order.0.dir', 'asc');
        $search = $request->input('search.value');

        // 3. Mulai Query
        $query = Amenity::query();

        // 4. Filter Pencarian Global
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'LIKE', "%{$search}%")
                  ->orWhere('satuan', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        }

        // 5. Hitung Total Data (Untuk Pagination)
        $totalData = Amenity::count();
        $totalFiltered = $query->count();

        // 6. Ambil Data dengan Logika Sorting Custom (Stok < 5 Prioritas)
        $models = $query
            ->select('*', DB::raw('CASE WHEN stok < 5 THEN 0 ELSE 1 END as stock_priority'))
            ->orderBy('stock_priority', 'asc') // Stok kritis muncul duluan
            ->orderBy($order, $dir)            // Sorting user (klik header tabel)
            ->offset($start)
            ->limit($limit)
            ->get();

        // 7. Format Data
        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'nama_barang' => $model->nama_barang,
                'stok' => $model->stok,
                'satuan' => $model->satuan,
                'keterangan' => $model->keterangan ?? '-',
                'is_low_stock' => $model->stok < 5, // Flag untuk warna merah di frontend
            ];
        }

        // 8. Return ARRAY (Jangan json_encode manual disini, biar Controller yang urus)
        // Kita pakai format legacy (iTotalRecords, aaData) agar sama persis dengan Ingredient
        return [
            'draw' => intval($request->input('draw')),
            'iTotalRecords' => $totalData,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData' => $data, 
        ];
    }
}
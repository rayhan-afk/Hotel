<?php

namespace App\Repositories\Implementation;

use App\Models\Amenity;
use App\Repositories\Interface\AmenityRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AmenityRepository implements AmenityRepositoryInterface
{
    public function getAmenities($request)
    {
        return Amenity::orderBy('nama_barang')->get();
    }

    public function getAmenitiesDatatable($request)
    {
        // Mapping urutan kolom untuk sorting dari DataTables
        $columns = [
            0 => 'nama_barang',
            1 => 'stok',
            2 => 'satuan',
            3 => 'stok',       // Kolom Status (disortir berdasarkan stok)
            4 => 'keterangan',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'stok';
        $dir = $request->input('order.0.dir') ?? 'asc';
        $search = $request->input('search.value');

        $main_query = Amenity::select(
            'id',
            'nama_barang',
            'satuan',
            'stok',
            'keterangan'
        );

        $totalData = Amenity::count();

        // Logika Pencarian
        $main_query->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'LIKE', "%{$search}%")
                  ->orWhere('satuan', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan', 'LIKE', "%{$search}%");
            });
        });

        $totalFiltered = $main_query->count();

        // === FITUR BARU: PRIORITAS STOK < 5 MUNCUL PALING ATAS ===
        $models = $main_query
            ->select('*', DB::raw('CASE WHEN stok < 5 THEN 0 ELSE 1 END as stock_priority'))
            ->orderBy('stock_priority', 'asc') // Stok < 5 (priority 0) di atas
            ->orderBy($order, $dir) // Lalu sort sesuai user
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'nama_barang' => $model->nama_barang,
                'stok' => $model->stok,
                'satuan' => $model->satuan,
                'keterangan' => $model->keterangan ?? '-',
                // === FLAG UNTUK WARNING ROW (stok < 5) ===
                'is_low_stock' => $model->stok < 5,
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
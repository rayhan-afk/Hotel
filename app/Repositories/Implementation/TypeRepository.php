<?php

namespace App\Repositories\Implementation;

use App\Models\Type;
use App\Repositories\Interface\TypeRepositoryInterface;

class TypeRepository implements TypeRepositoryInterface
{
    // Method lama (biarkan saja jika masih dipakai di tempat lain)
    public function showAll($request)
    {
        $types = Type::orderBy('id', 'DESC');
        if (! empty($request->search)) {
            $types = $types->where('name', 'LIKE', '%'.$request->search.'%');
        }
        $types = $types->paginate(5);
        $types->appends($request->all());

        return $types;
    }

    /**
     * Method baru yang diperbaiki untuk Datatable Server-side
     * Menggunakan format return array standard (bukan json_encode manual)
     */
    public function getTypesDatatable($request)
    {
        // 1. Definisi Kolom untuk Sorting
        // Urutan harus sama dengan kolom di Javascript (type.js)
        $columns = [
            0 => 'id',          // Kolom No (Mapping ke ID atau Number)
            1 => 'name',        // Kolom Nama
            2 => 'information', // Kolom Informasi
            3 => 'id',          // Kolom Aksi
        ];

        // 2. Query Dasar
        $query = Type::query();

        // 3. Pencarian Global (Search)
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('information', 'LIKE', "%{$search}%");
            });
        }

        // 4. Sorting
        $orderIndex = $request->input('order.0.column', 0); // Ambil index kolom yang diklik
        $orderDir = $request->input('order.0.dir', 'desc'); // Ambil arah sort (asc/desc)
        $orderColumn = $columns[$orderIndex] ?? 'id';       // Default sort by ID
        
        $query->orderBy($orderColumn, $orderDir);

        // 5. Hitung Total Data (Penting untuk Pagination)
        $totalData = Type::count();       // Total semua data di DB
        $totalFiltered = $query->count(); // Total data setelah difilter search

        // 6. Pagination (Limit & Offset)
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        
        $models = $query->offset($start)->limit($limit)->get();

        // 7. Mapping Data (Agar sesuai dengan key yang diminta JS: 'number', 'name', dll)
        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'number'      => $model->id,          // Key 'number' untuk kolom nomor
                'name'        => $model->name,        // Key 'name'
                'information' => $model->information, // Key 'information'
                'id'          => $model->id,          // Key 'id' untuk tombol aksi
            ];
        }

        // 8. Return Array (Controller akan otomatis mengubahnya jadi JSON)
        // Format ini SAMA PERSIS dengan CustomerRepository
        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data
        ];
    }

    public function store($typeData)
    {
        $type = new Type;
        $type->name = $typeData->name;
        $type->information = $typeData->information;
        $type->save();

        return $type;
    }

    public function getTypeList($request)
    {
        return Type::all();
    }
}
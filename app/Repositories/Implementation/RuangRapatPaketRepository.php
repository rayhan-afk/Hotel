<?php

namespace App\Repositories\Implementation;

use App\Models\RuangRapatPaket;
use App\Repositories\Interface\RuangRapatPaketRepositoryInterface;

class RuangRapatPaketRepository implements RuangRapatPaketRepositoryInterface
{
    public function getPakets($request)
    {
        // Jika diperlukan untuk tampilan non-datatable
        return RuangRapatPaket::orderBy('name')->get();
    }

    public function getPaketsDatatable($request)
    {
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'isi_paket',
            3 => 'fasilitas',
            4 => 'harga',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $order = $columns[$orderColumnIndex] ?? 'name';
        $dir = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $query = RuangRapatPaket::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('isi_paket', 'LIKE', "%{$search}%")
                  ->orWhere('fasilitas', 'LIKE', "%{$search}%");
            });
        }

        $totalData = RuangRapatPaket::count();
        $totalFiltered = $query->count();

        $models = $query->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];
        foreach ($models as $model) {
            // Format harga ke format Rupiah jika diperlukan, atau biarkan angka mentah
            $data[] = [
                'id' => $model->id,
                'name' => $model->name,
                'isi_paket' => $model->isi_paket,
                'fasilitas' => $model->fasilitas,
                'harga' => number_format($model->harga, 0, ',', '.'), // Format tampilan harga
                // Action buttons akan ditangani di JS atau bisa dirender di sini jika mau
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
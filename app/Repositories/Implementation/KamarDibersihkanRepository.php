<?php

namespace App\Repositories\Implementation;

use App\Models\Room;
use App\Repositories\Interface\KamarDibersihkanRepositoryInterface;
use Illuminate\Http\Request;

class KamarDibersihkanRepository implements KamarDibersihkanRepositoryInterface
{
    public function getDatatable(Request $request)
    {
        try {
            // 1. Query Dasar
            $query = Room::where('status', 'Cleaning')->with('type');

            // 2. Pencarian
            if ($request->has('search') && !empty($request->input('search.value'))) {
                $search = $request->input('search.value');
                $query->where(function($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                      ->orWhereHas('type', function($t) use ($search) {
                          $t->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // 3. Hitung Total
            $totalData = Room::where('status', 'Cleaning')->count();
            $totalFiltered = $query->count();

            // 4. Pagination
            $limit = $request->input('length') != -1 ? $request->input('length') : 10;
            $start = $request->input('start') ? $request->input('start') : 0;
            
            $rooms = $query->orderBy('updated_at', 'DESC')
                           ->offset($start)
                           ->limit($limit)
                           ->get();

            // 5. Format Data
            $data = [];
            foreach ($rooms as $index => $room) {
                // Kita kirim 'id' agar JS bisa membacanya via row.id
                $data[] = [
                    'id'          => $room->id, // <--- WAJIB ADA
                    'DT_RowIndex' => $start + $index + 1,
                    'number'      => $room->number,
                    'type_name'   => $room->type ? $room->type->name : 'N/A',
                    'status'      => 'Cleaning',
                    // Action button dikirim kosong pun tidak apa-apa karena dirender ulang oleh JS, 
                    // tapi kita isi sebagai cadangan.
                    'action'      => '' 
                ];
            }

            return [
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data'            => $data
            ];

        } catch (\Exception $e) {
            return [
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => $e->getMessage()
            ];
        }
    }
}
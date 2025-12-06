<?php

namespace App\Repositories\Implementation;

use App\Models\Room;
use App\Repositories\Interface\KamarTersediaRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KamarTersediaRepository implements KamarTersediaRepositoryInterface
{
    public function getDatatable(Request $request)
    {
        // Definisi Kolom untuk Sorting (Sesuai urutan di JS)
        $columns = [
            0 => 'rooms.number',
            1 => 'rooms.number',
            2 => 'rooms.name',
            3 => 'types.name',
            4 => 'rooms.area_sqm',
            5 => 'rooms.room_facilities',
            6 => 'rooms.bathroom_facilities',
            7 => 'rooms.capacity',
            8 => 'rooms.price',
            9 => 'rooms.id', // Status
        ];

        // 1. QUERY UTAMA
        $query = Room::query()
            ->select([
                'rooms.*',
                'types.name as type_name' // Alias untuk sorting
            ])
            ->join('types', 'rooms.type_id', '=', 'types.id')
            ->distinct();

        // 2. LOGIKA KETERSEDIAAN (Hanya ambil yang kosong hari ini)
        $query->whereDoesntHave('transactions', function($q) {
            $today = Carbon::today();
            $q->where(function($sub) use ($today) {
                $sub->whereDate('check_in', '<=', $today)
                    ->whereDate('check_out', '>', $today);
            })
            ->whereNotIn('status', ['Cancel', 'Checked Out']);
        });

        // 3. FILTER TIPE
        if ($request->has('type') && $request->type != 'All') {
            $query->where('rooms.type_id', $request->type);
        }

        // 4. SEARCHING GLOBAL
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.name', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%");
            });
        }

        // 5. SORTING & PAGINATION
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIdx = $request->input('order.0.column', 0);
        $orderCol = $columns[$orderIdx] ?? 'rooms.number';
        $orderDir = $request->input('order.0.dir', 'asc');

        $countQuery = clone $query;
        $totalFiltered = $countQuery->count();

        $models = $query->orderBy($orderCol, $orderDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        // 6. FORMAT DATA (Flat Structure mirip RoomRepository)
        $data = [];
        foreach ($models as $room) {
            $data[] = [
                'id'                  => $room->id,
                'number'              => $room->number,
                'name'                => $room->name,
                'type'                => $room->type_name,
                'area_sqm'            => $room->area_sqm,
                'room_facilities'     => $room->room_facilities,
                'bathroom_facilities' => $room->bathroom_facilities,
                'capacity'            => $room->capacity,
                'price'               => $room->price,
                // Status akan dirender di JS sebagai 'Tersedia'
            ];
        }

        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => Room::count(),
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ];
    }
}
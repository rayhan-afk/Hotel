<?php

namespace App\Repositories\Implementation;

use App\Models\Room;
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;

class RoomRepository implements RoomRepositoryInterface
{
    public function getRooms(Request $request)
    {
        // Metode ini untuk pagination biasa (jika dipakai selain di Datatable)
        return Room::with(['type'])
            ->orderBy('number')
            ->when($request->type && $request->type !== 'All', function ($query) use ($request) {
                $query->where('type_id', $request->type);
            })
            ->paginate(5);
    }

    public function getRoomsDatatable(Request $request)
    {
        // 1. Definisi Kolom (Harus sinkron dengan JS)
        $columns = [
            0 => 'rooms.number',
            1 => 'rooms.name',
            2 => 'types.name',
            3 => 'rooms.area_sqm',
            4 => 'rooms.room_facilities',
            5 => 'rooms.bathroom_facilities',
            6 => 'rooms.capacity',
            7 => 'rooms.price',
            8 => 'rooms.id',
        ];

        // 2. Ambil Parameter Datatable
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 0);
        $order = $columns[$orderColumnIndex] ?? 'rooms.number';
        $dir = $request->input('order.0.dir', 'asc');
        $search = $request->input('search.value');

        // 3. Query Builder
        $query = Room::select('rooms.*', 'types.name as type_name')
            ->leftJoin('types', 'rooms.type_id', '=', 'types.id');

        // 4. Filter: Tipe Kamar
        if ($request->has('type') && $request->type != 'All') {
            $query->where('rooms.type_id', $request->type);
        }

        // 5. Filter: Pencarian Global
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.name', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%");
            });
        }

        // 6. Hitung Total Data (Sebelum Pagination)
        $totalData = Room::count();
        $totalFiltered = $query->count();

        // 7. Ambil Data (Dengan Pagination & Sorting)
        $models = $query->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        // 8. Format Data menjadi Array untuk JSON
        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'number' => $model->number,
                'name' => $model->name,
                'type' => $model->type_name, // Dari Alias Select
                'area_sqm' => $model->area_sqm,
                'room_facilities' => $model->room_facilities,
                'bathroom_facilities' => $model->bathroom_facilities,
                'capacity' => $model->capacity,
                'price' => $model->price,
            ];
        }

        // 9. Return Struktur JSON Standar Datatable
        return [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData, // Perhatikan nama key standar ini
            'recordsFiltered' => $totalFiltered, // Perhatikan nama key standar ini
            'data' => $data, // Ubah dari 'aaData' ke 'data' (versi Datatable baru lebih suka 'data')
        ];
    }

    public function getRoomById($id) { return Room::findOrFail($id); }
    
    public function store(Request $request) 
    { 
        // Handle File Upload (Gambar)
        $data = $request->all();
        if ($request->hasFile('image')) {
            // Simpan file dan ambil path-nya
            $path = $request->file('image')->store('img/rooms', 'public');
            // Tambahkan path ke kolom database
            $data['main_image_path'] = 'storage/' . $path;
        }

        return Room::create($data); 
    }
    
    public function update($room, Request $request) 
    { 
        $data = $request->all();
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('img/rooms', 'public');
            $data['main_image_path'] = 'storage/' . $path;
        }
        
        $room->update($data); 
        return $room; 
    }
    
    public function delete($room) { $room->delete(); }
}
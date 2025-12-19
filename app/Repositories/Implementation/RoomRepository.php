<?php

namespace App\Repositories\Implementation;

use App\Models\Room;
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; // ✅ PENTING: Tambahkan ini agar bisa hapus/buat folder

class RoomRepository implements RoomRepositoryInterface
{
    public function getRooms(Request $request)
    {
        return Room::with(['type'])
            ->orderBy('number')
            ->when($request->type && $request->type !== 'All', function ($query) use ($request) {
                $query->where('type_id', $request->type);
            })
            ->paginate(5);
    }

    public function getRoomsDatatable(Request $request)
    {
        // Bagian ini TETAP SAMA seperti kode asli Anda
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

        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 0);
        $order = $columns[$orderColumnIndex] ?? 'rooms.number';
        $dir = $request->input('order.0.dir', 'asc');
        $search = $request->input('search.value');

        $query = Room::select('rooms.*', 'types.name as type_name')
            ->leftJoin('types', 'rooms.type_id', '=', 'types.id');

        if ($request->has('type') && $request->type != 'All') {
            $query->where('rooms.type_id', $request->type);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.name', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = Room::count();
        $totalFiltered = $query->count();

        $models = $query->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'number' => $model->number,
                'name' => $model->name,
                'type' => $model->type_name,
                'area_sqm' => $model->area_sqm,
                'room_facilities' => $model->room_facilities,
                'bathroom_facilities' => $model->bathroom_facilities,
                'capacity' => $model->capacity,
                'price' => $model->price,
            ];
        }

        return [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
    }

    public function getRoomById($id) { return Room::findOrFail($id); }

    /**
     * ✅ FITUR BARU: Helper Upload Image
     * Method ini bisa dipanggil oleh Controller (untuk Approval) atau Repository (untuk Store/Update)
     */
    public function uploadImage($file, $room)
    {
        // 1. Tentukan path folder: public/img/room/ID-SlugNama
        $folderName = $room->id . '-' . Str::slug($room->name);
        $destinationPath = public_path('img/room/' . $folderName);

        // 2. Buat folder jika belum ada
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // 3. Pindahkan file fisik
        $fileName = $file->getClientOriginalName();
        $file->move($destinationPath, $fileName);

        // Kembalikan nama file saja
        return $fileName;
    }
    
    // ==========================================
    // BAGIAN STORE & UPDATE (SUDAH DISEDERHANAKAN)
    // ==========================================

    public function store(Request $request) 
    { 
        // 1. Simpan Data Kamar Dulu
        $data = $request->except('image');
        $room = Room::create($data);

        // 2. Upload Gambar (Pakai Helper)
        if ($request->hasFile('image')) {
            $fileName = $this->uploadImage($request->file('image'), $room);
            
            // Update Database
            $room->main_image_path = $fileName;
            $room->save();
        }

        return $room; 
    }
    
    public function update($room, Request $request) 
    { 
        // 1. Simpan nama folder lama untuk rename nanti
        $oldFolderName = $room->id . '-' . Str::slug($room->name);
        
        // Update data dasar
        $data = $request->except('image');
        $room->update($data);

        // 2. Cek apakah perlu rename folder (jika nama kamar berubah)
        $newFolderName = $room->id . '-' . Str::slug($room->name);
        
        $oldPath = public_path('img/room/' . $oldFolderName);
        $newPath = public_path('img/room/' . $newFolderName);

        if ($oldFolderName !== $newFolderName && File::exists($oldPath)) {
            File::moveDirectory($oldPath, $newPath); // Rename folder fisik
        }

        // 3. Upload Gambar Baru (Pakai Helper)
        if ($request->hasFile('image')) {
            $fileName = $this->uploadImage($request->file('image'), $room);

            // Simpan nama file ke DB
            $room->main_image_path = $fileName;
            $room->save();
        }
        
        return $room; 
    }
    
    public function delete($room) { 
        // ✅ PERBAIKAN: Hapus folder fisik beserta isinya
        $folderName = $room->id . '-' . Str::slug($room->name);
        $path = public_path('img/room/' . $folderName);
        
        if (File::exists($path)) {  
            File::deleteDirectory($path);
        }

        $room->delete(); 
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Approval;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    private $roomRepository;

    public function __construct(RoomRepositoryInterface $roomRepository) 
    {
        $this->roomRepository = $roomRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->roomRepository->getRoomsDatatable($request);
            return response()->json($data);
        }

        return view('room.index');
    }

    public function create()
    {
        $types = Type::all();
        
        $view = view('room.create', [
            'types' => $types,
            'room' => null 
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        // Ambil semua data yang sudah tervalidasi
        $data = $request->validated();

        // === LOGIKA UPLOAD GAMBAR ===
        if ($request->hasFile('image')) {
            // 1. Ambil file
            $file = $request->file('image');
            
            // 2. Buat nama file unik (biar tidak tertimpa)
            // Contoh: room_101_1699999999.jpg
            $filename = 'room_' . $data['number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // 3. Simpan file ke folder 'public/img/rooms'
            // Menggunakan disk 'public' agar bisa diakses dari web
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // 4. Simpan path yang bisa diakses publik ke database
            // 'storage/img/rooms/namafile.jpg'
            $data['main_image_path'] = 'storage/' . $path;
        }

        // Simpan data ke database menggunakan Eloquent langsung atau Repository
        // Karena repository Anda mungkin belum support array $data yang dimodifikasi,
        // kita pakai Room::create() langsung di sini agar aman dan cepat.
        Room::create($data);

        return response()->json([
            'message' => 'Room created successfully',
        ]);
    }

    public function show(Room $room)
    {
        return view('room.show', [
            'room' => $room,
        ]);
    }

    public function edit(Room $room)
    {
        $types = Type::all();
        
        $view = view('room.create', [
            'room' => $room,
            'types' => $types,
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    // === [UPDATE: LOGIC APPROVAL] ===
    public function update(Room $room, StoreRoomRequest $request)
    {
        // 1. Validasi Data
        $data = $request->validated();

        // 2. Handle Upload Gambar Baru (Jika Ada)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'room_' . $data['number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan gambar baru ke folder public
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // Masukkan path baru ke array data yang akan di-request
            $data['main_image_path'] = 'storage/' . $path;
        }

        // 3. Persiapkan Data Lama (Untuk history & mencegah error old_data required)
        $oldData = $room->toArray();

        // 4. Cek apakah user adalah Manager atau Super (bisa langsung update tanpa approval)
        if (auth()->user()->hasRole(['Super', 'Manager'])) {
            // === LOGIKA UPDATE LANGSUNG (Untuk Manager/Super) ===
            
            // Hapus gambar lama jika ada dan ada gambar baru yang diupload (Opsional tapi disarankan biar storage gak penuh)
            if ($request->hasFile('image') && $room->main_image_path) {
                // Ubah path 'storage/img/...' menjadi path relatif storage asli 'img/...'
                $oldPath = str_replace('storage/', '', $room->main_image_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Update room langsung
            $room->update($data);

            return response()->json([
                'message' => 'Room updated successfully!',
            ]);
        } else {
            // === LOGIKA APPROVAL (Untuk Admin yang butuh persetujuan) ===
            
            // BUAT TICKET APPROVAL
            Approval::create([
                'type' => 'room',
                'reference_id' => $room->id,
                'requested_by' => auth()->id(), // Pastikan kolom ini benar di DB
                'new_data' => $data,
                'old_data' => $oldData,         // Kirim data lama juga
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Permintaan perubahan kamar berhasil diajukan ke Manager!',
            ]);
        }
    }

    public function destroy(Room $room)
    {
        try {
            // Hapus gambar jika ada
            if ($room->main_image_path) {
                $oldPath = str_replace('storage/', '', $room->main_image_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Hapus data kamar
            $room->delete();

            return response()->json([
                'message' => 'Room deleted successfully!',
            ]);
        } catch (\Exception $e) {
            // Return error 500 agar masuk ke blok catch di JS
            return response()->json([
                'message' => 'Error deleting room: ' . $e->getMessage()
            ], 500);
        }
    }
}
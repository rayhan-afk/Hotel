<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Models\Approval;
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $data = $request->validated();

        // === LOGIKA UPLOAD GAMBAR ===
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Nama file unik
            $filename = 'room_' . $data['number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Simpan ke storage/app/public/img/rooms
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // Simpan path dengan prefix 'storage/'
            // ✅ SESUAIKAN dengan nama kolom di tabel rooms Anda
            // $data['image_url'] = 'storage/' . $path;
            // Jika kolom DB Anda namanya 'image', gunakan:
            // $data['image'] = 'storage/' . $path;
            // Jika kolom DB Anda namanya 'main_image_path', gunakan:
            $data['main_image_path'] = 'storage/' . $path;
        }

        Room::create($data);

        return response()->json([
            'message' => 'Kamar berhasil ditambahkan!',
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

    // === [UPDATE: LOGIC APPROVAL - FIXED] ===
    public function update(Room $room, StoreRoomRequest $request)
    {
        // 1. Validasi Data
        $data = $request->validated();
        
        // ✅ PERBAIKAN 1: Ambil path gambar lama dengan lebih robust
        // Cek kolom mana yang dipakai di database Anda
        $oldImage = $room->main_image_path ?? $room->image ?? $room->main_image_path ?? null;

        // 2. Handle Upload Gambar Baru (Jika Ada)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'room_' . $data['number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Upload gambar baru ke storage/app/public/img/rooms
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // ✅ PERBAIKAN 2: Set ke kolom yang benar dengan prefix 'storage/'
            // PENTING: Sesuaikan 'image_url' dengan nama kolom di tabel rooms Anda
            // $data['image_url'] = 'storage/' . $path;
            
            // Jika kolom DB Anda namanya 'image', gunakan:
            // $data['image'] = 'storage/' . $path;
            
            // Jika kolom DB Anda namanya 'main_image_path', gunakan:
            $data['main_image_path'] = 'storage/' . $path;
        }

        // 3. Persiapkan Data Lama (Untuk history approval)
        $oldData = $room->toArray();
        
        // ✅ PERBAIKAN 3: Pastikan image_url ada di oldData
        // Karena toArray() mungkin tidak include accessor, tambahkan manual
        if (!isset($oldData['main_image_path']) && $oldImage) {
            $oldData['main_image_path'] = $oldImage;
        }
        
        // Jika kolom DB Anda 'image', gunakan:
        // if (!isset($oldData['image']) && $oldImage) {
        //     $oldData['image'] = $oldImage;
        // }
        
        // Jika kolom DB Anda 'main_image_path', gunakan:
        // if (!isset($oldData['main_image_path']) && $oldImage) {
        //     $oldData['main_image_path'] = $oldImage;
        // }

        // 4. Cek Role User
        $user = Auth::user();

        if ($user->role === 'Super' || $user->role === 'Manager') {
            // === JALUR MANAGER/SUPER (LANGSUNG UPDATE) ===
            
            // Update Data Kamar
            $room->update($data);

            // Hapus gambar lama HANYA JIKA update berhasil DAN ada gambar baru
            if ($request->hasFile('image') && $oldImage) {
                // Bersihkan path dari prefix 'storage/' jika ada
                $cleanPath = str_replace('storage/', '', $oldImage);
                
                // Hapus file fisik jika ada
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            }

            return response()->json([
                'message' => 'Data kamar berhasil diperbarui!',
            ]);

        } else {
            // === JALUR ADMIN/STAFF (BUTUH APPROVAL) ===
            
            // ✅ PERBAIKAN 4: Log untuk debugging (opsional, bisa dihapus nanti)
            Log::info('Room Approval Created', [
                'room_id' => $room->id,
                'old_image' => $oldData['main_image_path'] ?? 'tidak ada',
                'new_image' => $data['main_image_path'] ?? 'tidak ada',
                'has_file_upload' => $request->hasFile('image')
            ]);
            
            // Buat Ticket Approval
            Approval::create([
                'type' => 'room',
                'reference_id' => $room->id,
                'requested_by' => $user->id,
                'new_data' => $data, // ✅ Sekarang sudah include image_url
                'old_data' => $oldData, // ✅ Sekarang sudah include image_url
                'status' => 'pending' // ✅ Perbaiki: huruf kecil 'pending'
            ]);

            return response()->json([
                'message' => 'Perubahan diajukan! Menunggu persetujuan Manager.',
            ]);
        }
    }

   public function destroy(Room $room)
    {
        // Cek permission manual (Opsional)
        if (!in_array(Auth::user()->role, ['Super', 'Manager'])) {
            return response()->json(['message' => 'Hanya Manager yang dapat menghapus kamar!'], 403);
        }

        try {
            // ✅ Ambil path gambar dengan robust
            $imagePath = $room->main_image_path ?? $room->image ?? $room->main_image_path ?? null;

            // Hapus data kamar dari DB
            $room->delete();

            // Hapus file gambar fisik SETELAH data berhasil dihapus
            if ($imagePath) {
                // Bersihkan path dari prefix 'storage/' jika ada
                $cleanPath = str_replace('storage/', '', $imagePath);
                
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            }

            return response()->json([
                'message' => 'Kamar berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            // === PENANGANAN ERROR DATABASE ===
            
            // Kode Error 23000 biasanya adalah Integrity Constraint Violation (Foreign Key)
            if ($e->getCode() == "23000") {
                return response()->json([
                    'message' => 'Data tidak dapat dihapus karena kamar ini memiliki riwayat transaksi/reservasi. Menghapus data ini akan merusak laporan keuangan.'
                ], 409); // 409 Conflict
            }

            // Error database lain
            return response()->json([
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ], 500);
        }
    }
}
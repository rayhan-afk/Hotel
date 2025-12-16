<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Models\Approval;
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Jangan lupa ini

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
        $view = view('room.create', ['types' => $types, 'room' => null])->render();
        return response()->json(['view' => $view]);
    }

    public function store(StoreRoomRequest $request)
    {
        // Langsung serahkan ke repository (sudah aman)
        $this->roomRepository->store($request);

        return response()->json([
            'message' => 'Kamar berhasil ditambahkan!',
        ]);
    }

    public function show(Room $room)
    {
        return view('room.show', ['room' => $room]);
    }

    public function edit(Room $room)
    {
        $types = Type::all();
        $view = view('room.create', ['room' => $room, 'types' => $types])->render();
        return response()->json(['view' => $view]);
    }

    // === PERBAIKAN: STRUKTUR LENGKAP TAPI PAKAI REPO ===
    public function update(Room $room, StoreRoomRequest $request)
    {
        // 1. Validasi Data
        $data = $request->validated();
        
        // [PENTING] Ambil path gambar lama dengan robust (Logic Asli Kamu)
        $oldImage = $room->main_image_path ?? $room->image ?? null;

        // 2. Persiapkan Data Lama untuk History (Logic Asli Kamu)
        $oldData = $room->toArray();
        
        // Pastikan image path masuk ke oldData meskipun accessor belum di-load
        if (!isset($oldData['main_image_path']) && $oldImage) {
            $oldData['main_image_path'] = $oldImage;
        }

        $user = Auth::user();

        // === SKENARIO 1: MANAGER/SUPER (Langsung Update) ===
        if ($user->role === 'Super' || $user->role === 'Manager') {
            
            // Kita panggil Repository update.
            // Repository ini sudah pintar: dia akan handle rename folder,
            // hapus file lama, dan upload file baru secara otomatis.
            $this->roomRepository->update($room, $request);

            return response()->json([
                'message' => 'Data kamar berhasil diperbarui!',
            ]);

        } 
        // === SKENARIO 2: ADMIN/STAFF (Butuh Approval) ===
        else {
            
            // Handle Upload Gambar untuk Approval
            // Di sini kita "meminjam" method uploadImage dari repository
            // supaya path foldernya konsisten (public_html/img/room/ID-Slug)
            if ($request->hasFile('image')) {
                
                // Panggil Helper Repo (Cukup 1 baris, folder otomatis dibuat)
                $filename = $this->roomRepository->uploadImage($request->file('image'), $room);
                
                // Simpan nama file ke array data untuk approval
                $data['main_image_path'] = $filename;
                
                // Hapus object file asli agar tidak error saat create JSON
                unset($data['image']);
            }

            // [PENTING] Log Debugging (Logic Asli Kamu Tetap Ada)
            Log::info('Room Approval Created', [
                'room_id' => $room->id,
                'requester' => $user->name,
                'old_image' => $oldData['main_image_path'] ?? 'tidak ada',
                'new_image' => $data['main_image_path'] ?? 'tidak ada',
                'has_file'  => $request->hasFile('image')
            ]);
            
            // Buat Ticket Approval
            Approval::create([
                'type' => 'room',
                'reference_id' => $room->id,
                'requested_by' => $user->id,
                'new_data' => $data,
                'old_data' => $oldData,
                'status' => 'Pending' // Sesuaikan enum di DB (Pending/pending)
            ]);

            return response()->json([
                'message' => 'Perubahan diajukan! Menunggu persetujuan Manager.',
            ]);
        }
    }

    public function destroy(Room $room)
    {
        if (!in_array(Auth::user()->role, ['Super', 'Manager'])) {
            return response()->json(['message' => 'Hanya Manager yang dapat menghapus kamar!'], 403);
        }

        try {
            // Panggil Repository delete (Folder fisik ikut terhapus di sana)
            $this->roomRepository->delete($room);

            return response()->json([
                'message' => 'Kamar berhasil dihapus!',
            ]);

        } catch (\Exception $e) {
            // Handle Error Foreign Key (Logic Asli Kamu)
            if ($e->getCode() == "23000") {
                return response()->json([
                    'message' => 'Data tidak dapat dihapus karena kamar ini memiliki riwayat transaksi/reservasi.'
                ], 409);
            }

            return response()->json([
                'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
            ], 500);
        }
    }
}
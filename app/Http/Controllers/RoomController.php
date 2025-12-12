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
            // $path akan berisi: "img/rooms/room_101_123456.jpg"
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // PERBAIKAN: Simpan path relatif saja, jangan pakai 'storage/' di depannya.
            // Biarkan Model Accessor (getImage) yang menambahkan 'storage/' saat ditampilkan.
            $data['image'] = $path; // Pastikan nama kolom di DB adalah 'image' atau 'main_image_path'
            // Jika kolom di DB namanya 'main_image_path', gunakan baris bawah ini:
            // $data['main_image_path'] = $path;
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

    // === [UPDATE: LOGIC APPROVAL] ===
    public function update(Room $room, StoreRoomRequest $request)
    {
        // 1. Validasi Data
        $data = $request->validated();
        $oldImage = $room->image ?? $room->main_image_path; // Ambil path gambar lama

        // 2. Handle Upload Gambar Baru (Jika Ada)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'room_' . $data['number'] . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Upload gambar baru
            $path = $file->storeAs('img/rooms', $filename, 'public');
            
            // Simpan path relatif ke array data
            // Sesuaikan nama kolom DB Anda ('image' atau 'main_image_path')
            if (isset($room->image)) {
                $data['image'] = $path;
            } else {
                $data['main_image_path'] = $path;
            }
        }

        // 3. Persiapkan Data Lama (Untuk history approval)
        $oldData = $room->toArray();

        // 4. Cek Role User
        // Menggunakan helper Auth::user() agar lebih aman
        $user = Auth::user();

        if ($user->role === 'Super' || $user->role === 'Manager') {
            // === JALUR MANAGER/SUPER (LANGSUNG UPDATE) ===
            
            // Update Data Kamar
            $room->update($data);

            // Hapus gambar lama HANYA JIKA update berhasil DAN ada gambar baru
            if ($request->hasFile('image') && $oldImage) {
                // Hapus file fisik jika ada
                if (Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            return response()->json([
                'message' => 'Data kamar berhasil diperbarui!',
            ]);

        } else {
            // === JALUR ADMIN/STAFF (BUTUH APPROVAL) ===
            
            // Buat Ticket Approval
            Approval::create([
                'type' => 'room', // Pastikan ini sesuai dengan logic di ApprovalController
                'reference_id' => $room->id,
                'requested_by' => $user->id,
                'new_data' => $data, // Data baru (termasuk path gambar baru jika ada)
                'old_data' => $oldData,
                'status' => 'Pending'
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
            // Ambil path gambar
            // Sesuaikan kolom DB Anda
            $imagePath = $room->image ?? $room->main_image_path;

            // Hapus data kamar dari DB
            $room->delete();

            // Hapus file gambar fisik SETELAH data berhasil dihapus (agar aman)
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
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

        } catch (\Exception $e) {
            // Error umum lainnya
            return response()->json([
                'message' => 'Gagal menghapus kamar. Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
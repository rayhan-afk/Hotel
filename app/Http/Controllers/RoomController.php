<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\Type;
use App\Models\Approval;
use App\Models\Amenity; // [BARU] Import Model Amenity
use App\Repositories\Interface\RoomRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        $types = Type::with('rooms.amenities')->get(); 
        $amenities = Amenity::all();

        return view('room.index', compact('types', 'amenities'));
    }

    public function create()
    {
        $types = Type::all();
        
        // [BARU] Ambil Amenities (Kecuali Literan) untuk Checkbox
        $amenities = Amenity::where('satuan', '!=', 'liter')->get();

        $view = view('room.create', [
            'types' => $types, 
            'room' => null,
            'amenities' => $amenities // [BARU] Kirim ke View
        ])->render();

        return response()->json(['view' => $view]);
    }

    public function store(StoreRoomRequest $request)
    {
        // 1. Simpan Kamar via Repository
        // Pastikan repository kamu me-return object Room yang baru dibuat
        $room = $this->roomRepository->store($request);

        // 2. [BARU] Simpan Amenities (Jatah Barang)
        // Kita panggil fungsi private di bawah agar kodingan rapi
        $this->syncAmenities($room, $request);

        return response()->json([
            'message' => 'Kamar dan fasilitas berhasil ditambahkan!',
        ]);
    }

    public function show(Room $room)
    {
        return view('room.show', ['room' => $room]);
    }

    public function edit(Room $room)
    {
        $types = Type::all();
        
        // [BARU] Ambil Amenities untuk Checkbox di Edit
        $amenities = Amenity::where('satuan', '!=', 'liter')->get();

        $view = view('room.create', [
            'room' => $room, 
            'types' => $types,
            'amenities' => $amenities // [BARU] Kirim ke View
        ])->render();
        
        return response()->json(['view' => $view]);
    }

    // === PERBAIKAN: INTEGRASI AMENITIES + APPROVAL ===
    public function update(Room $room, StoreRoomRequest $request)
    {
        // 1. Validasi Data Dasar
        $data = $request->validated();
        
        // [BARU] Masukkan Data Amenities ke array $data
        // Ini penting agar data amenities ikut tersimpan di tabel Approval (JSON)
        // jika yang edit adalah Admin biasa.
        $data['amenities'] = $request->input('amenities', []);
        $data['amounts']   = $request->input('amounts', []);

        // Ambil path gambar lama
        $oldImage = $room->main_image_path ?? $room->image ?? null;

        // 2. Persiapkan Data Lama untuk History
        $oldData = $room->toArray();
        
        // [BARU] Masukkan juga data amenities lama ke history (biar manager tau bedanya)
        // Formatnya kita ambil ID-nya saja biar hemat space
        $oldData['amenities'] = $room->amenities->pluck('id')->toArray();
        
        if (!isset($oldData['main_image_path']) && $oldImage) {
            $oldData['main_image_path'] = $oldImage;
        }

        $user = Auth::user();

        // === SKENARIO 1: MANAGER/SUPER (Langsung Update) ===
        if ($user->role === 'Super' || $user->role === 'Manager') {
            
            // A. Update Fisik Kamar via Repository
            $this->roomRepository->update($room, $request);

            // B. [BARU] Update Jatah Amenities (Sync)
            $this->syncAmenities($room, $request);

            return response()->json([
                'message' => 'Data kamar & amenities berhasil diperbarui!',
            ]);

        } 
        // === SKENARIO 2: ADMIN/STAFF (Butuh Approval) ===
        else {
            
            // Handle Upload Gambar untuk Approval
            if ($request->hasFile('image')) {
                $filename = $this->roomRepository->uploadImage($request->file('image'), $room);
                $data['main_image_path'] = $filename;
                unset($data['image']);
            }

            Log::info('Room Approval Created', [
                'room_id' => $room->id,
                'requester' => $user->name,
                'amenities_count' => count($data['amenities']), // Log amenities
            ]);
            
            // Buat Ticket Approval
            Approval::create([
                'type' => 'room',
                'reference_id' => $room->id,
                'requested_by' => $user->id,
                'new_data' => $data, // Amenities & Amounts sudah masuk di sini
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
        if (!in_array(Auth::user()->role, ['Super', 'Manager'])) {
            return response()->json(['message' => 'Hanya Manager yang dapat menghapus kamar!'], 403);
        }

        try {
            $this->roomRepository->delete($room);

            return response()->json([
                'message' => 'Kamar berhasil dihapus!',
            ]);

        } catch (\Exception $e) {
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

    // === [HELPER PRIVATE] LOGIKA SYNC AMENITIES ===
    // Fungsi ini memisahkan logika yang rumit agar controller tetap bersih
    private function syncAmenities(Room $room, Request $request)
    {
        if ($request->has('amenities')) {
            $pivotData = [];
            
            foreach ($request->amenities as $amenityId) {
                // Ambil qty dari input array amounts[id]
                // Default 1 jika tidak diisi/kosong
                $qty = $request->amounts[$amenityId] ?? 1; 

                // Siapkan format untuk sync
                $pivotData[$amenityId] = ['amount' => $qty];
            }

            // Simpan ke database (Hapus yang tidak dicentang, update yang dicentang)
            $room->amenities()->sync($pivotData);
        } else {
            // Jika tidak ada checkbox dicentang, hapus semua relasi
            $room->amenities()->detach();
        }
    }

    // 1. Tampilkan Tabel Matriks (Barisnya TIPE KAMAR)
    public function bulkAmenities(Request $request)
    {
        $types = Type::with(['rooms.amenities'])->get();
        $amenities = Amenity::all(); 

        // Return view form terpisah (bukan index)
        return view('room.bulk_amenities', compact('types', 'amenities'));
    }

    // 2. Simpan Perubahan (Sekali simpan update ke SEMUA kamar di tipe itu)
  // Update Method Simpan (Versi Per Tipe Kamar)
    public function bulkAmenitiesUpdate(Request $request)
    {
        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            // Loop data berdasarkan Tipe Kamar
            foreach ($items as $typeId => $amenityData) {
                
                // 1. Siapkan data yang mau di-sync (hanya yang jumlahnya > 0)
                $syncData = [];
                foreach ($amenityData as $amenityId => $amount) {
                    if ($amount > 0) {
                        $syncData[$amenityId] = ['amount' => $amount];
                    }
                }

                // 2. Ambil semua kamar yang memiliki Tipe tersebut
                $rooms = Room::where('type_id', $typeId)->get();

                // 3. Terapkan amenities ke setiap kamar
                foreach ($rooms as $room) {
                    $room->amenities()->sync($syncData);
                }
            }

            DB::commit();
            
            // Redirect ke Index (Modal otomatis hilang karena page reload)
            return redirect()->route('room.index')
                             ->with('success', 'Berhasil! Setup Amenities per Tipe telah disimpan.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('room.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
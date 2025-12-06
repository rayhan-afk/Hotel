<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Repositories\Interface\KamarDibersihkanRepositoryInterface;
use Illuminate\Http\Request;

class KamarDibersihkanController extends Controller
{
    private $kamarDibersihkanRepository;

    public function __construct(KamarDibersihkanRepositoryInterface $kamarDibersihkanRepository)
    {
        $this->kamarDibersihkanRepository = $kamarDibersihkanRepository;
    }

    public function index(Request $request)
    {
        // Respon untuk permintaan AJAX (DataTables)
        if ($request->ajax()) {
            return response()->json(
                $this->kamarDibersihkanRepository->getDatatable($request)
            );
        }

        // Tampilan Halaman Utama
        return view('room-info.cleaning');
    }

    // Aksi Tombol Selesai
    public function finishCleaning($id)
    {
        $room = Room::findOrFail($id);
        
        $room->update([
            'status' => 'Available' // Kembalikan status kamar jadi Tersedia
        ]);

        return response()->json(['message' => 'Kamar ' . $room->number . ' sudah bersih dan siap digunakan!']);
    }
}
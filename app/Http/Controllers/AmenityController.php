<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Repositories\Interface\AmenityRepositoryInterface;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function __construct(
        private AmenityRepositoryInterface $amenityRepository
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->amenityRepository->getAmenitiesDatatable($request);
        }

        return view('amenity.index');
    }

    public function create()
    {
        // Render view partial untuk dimuat di Modal
        $view = view('amenity.create')->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi sederhana (bisa dipindah ke FormRequest terpisah seperti StoreRoomRequest)
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $amenity = Amenity::create($validated);

        return response()->json([
            'message' => 'Amenity ' . $amenity->nama_barang . ' created',
        ]);
    }

    public function edit(Amenity $amenity)
    {
        $view = view('amenity.edit', [
            'amenity' => $amenity,
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    public function update(Amenity $amenity, Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $amenity->update($validated);

        return response()->json([
            'message' => 'Amenity ' . $amenity->nama_barang . ' updated!',
        ]);
    }

    public function destroy(Amenity $amenity)
    {
        try {
            $amenity->delete();

            return response()->json([
                'message' => 'Amenity ' . $amenity->nama_barang . ' deleted!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Amenity cannot be deleted! Error Code:' . $e->errorInfo[1],
            ], 500);
        }
    }
}
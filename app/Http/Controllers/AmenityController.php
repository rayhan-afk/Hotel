<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

        return response()->json(['view' => $view]);
    }

    public function store(Request $request)
    {
        // Validasi sederhana
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        Amenity::create($validated);

        return response()->json(['message' => 'Amenity berhasil ditambahkan!']);
    }

    public function edit(Amenity $amenity)
    {
        $view = view('amenity.edit', compact('amenity'))->render();

        return response()->json(['view' => $view]);
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $amenity->update($validated);

        return response()->json(['message' => 'Amenity berhasil diperbarui!']);
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();
        return response()->json(['message' => 'Amenity berhasil dihapus!']);
    }
}
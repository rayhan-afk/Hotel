<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Repositories\Interface\AmenityRepositoryInterface;
// [PERBAIKAN 1] Gunakan nama Model yang benar sesuai file model Anda
use App\Models\StockOpnameAmenity; 
use Illuminate\Support\Facades\DB;
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
        $amenities = Amenity::orderBy('nama_barang', 'asc')->get();

        // Kirim variabel $amenities ke view menggunakan compact
        return view('amenity.index', compact('amenities'));
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

    public function stockOpname(Request $request)
    {
        $request->validate([
            'stocks'   => 'required|array',
            'stocks.*' => 'required|numeric|min:0',
            'notes'    => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->stocks as $id => $fisik) {
                
                $amenity = Amenity::find($id);

                if ($amenity) {
                    $stokSistem = $amenity->stok;
                    $stokBaru   = floatval($fisik);
                    $selisih    = $stokBaru - $stokSistem;

                    // Update Master Data
                    $amenity->stok = $stokBaru;
                    $amenity->save();

                    // Ambil Catatan
                    $catatan = isset($request->notes[$id]) ? $request->notes[$id] : null;

                    // [UPDATE] Simpan ke Database pakai Bahasa Inggris
                    StockOpnameAmenity::create([
                        'amenity_id'     => $amenity->id,
                        'system_stock'   => $stokSistem, // Before: stok_sistem
                        'physical_stock' => $stokBaru,   // Before: stok_fisik
                        'difference'     => $selisih,    // Before: selisih
                        'note'           => $catatan,    // Before: keterangan
                    ]);
                }
            }
            
            DB::commit();
            return response()->json(['message' => 'Stock Opname massal berhasil disimpan!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function history()
    {
        // Ambil data riwayat, urutkan dari yang terbaru
        // [PERBAIKAN 3] Panggil model yang benar (StockOpnameAmenity)
        $histories = StockOpnameAmenity::with('amenity')
                        ->orderBy('created_at', 'desc')
                        ->limit(100) 
                        ->get();

        // Render view khusus tabel history
        $view = view('amenity.history', compact('histories'))->render();

        return response()->json(['view' => $view]);
    }
}
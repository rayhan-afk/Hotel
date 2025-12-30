<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Repositories\Interface\AmenityRepositoryInterface;
use App\Models\AmenityStockOpname;
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
        // 1. Validasi Input (Sekarang menerima ARRAY)
        // 'stocks' adalah array, key-nya adalah ID amenity, value-nya jumlah stok
        $request->validate([
            'stocks'   => 'required|array',
            'stocks.*' => 'required|numeric|min:0', // Validasi tiap item stok
            'notes'    => 'nullable|array',         // Validasi array notes
        ]);

        DB::beginTransaction();
        try {
            // 2. Looping data yang dikirim dari form
            // $id = ID Amenity, $fisik = Jumlah Stok Fisik yang diinput
            foreach ($request->stocks as $id => $fisik) {
                
                // Cari barangnya
                $amenity = Amenity::find($id);

                // Jika barang ada, baru proses
                if ($amenity) {
                    $stokSistem = $amenity->stok;
                    $stokBaru   = floatval($fisik);
                    $selisih    = $stokBaru - $stokSistem;

                    // A. Update Stok di Master Data
                    $amenity->stok = $stokBaru;
                    $amenity->save();

                    // B. Ambil Catatan jika ada
                    $catatan = isset($request->notes[$id]) ? $request->notes[$id] : null;

                    // C. Simpan Riwayat (History)
                    // HANYA simpan jika ada perubahan stok ATAU user memaksa opname (opsional)
                    // Disini kita simpan semua agar terekam bahwa barang sudah dicek
                    AmenityStockOpname::create([
                        'amenity_id'     => $amenity->id,
                        'system_stock'   => $stokSistem,
                        'physical_stock' => $stokBaru,
                        'difference'     => $selisih,
                        'notes'          => $catatan,
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
    // Ganti 'StockOpname' jadi 'StockOpnameAmenity' (Model Amenity kamu)
    // Ganti relasi 'ingredient' jadi 'amenity'
    $histories = \App\Models\AmenityStockOpname::with('amenity')
                    ->orderBy('created_at', 'desc')
                    ->limit(100) // Batasi 100 terakhir
                    ->get();

    // Render view khusus tabel history (Pastikan path view benar)
    $view = view('amenity.history', compact('histories'))->render();

    return response()->json(['view' => $view]);
}
}
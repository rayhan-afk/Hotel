<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOpnameAmenity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log; // Tambahan untuk cek error di log

class LaporanStockopnameAmenities extends Controller
{
    public function exportPdf(Request $request)
    {
        // 1. Ambil input tanggal
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        // 2. Ambil data
        $data = StockOpnameAmenity::with('amenity')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->get();

        // --- PERBAIKAN 1: Cek apakah datanya ada? ---
        if ($data->isEmpty()) {
            // Jika kosong, jangan paksa download, kembalikan ke halaman sebelumnya dengan pesan
            return redirect()->back()->with('failed', 'Tidak ada data Laporan Opname pada tanggal tersebut.');
        }

        try {
            // 3. Load View PDF
            // Pastikan nama folder dan file view benar: resources/views/reports/pdf_stok_amenities.blade.php
            $pdf = Pdf::loadView('reports.pdf_stok_amenities', [
                'data'      => $data,
                'startDate' => $startDate,
                'endDate'   => $endDate
            ]);
    
            $pdf->setPaper('a4', 'portrait');
    
            $namaFile = 'Laporan_Opname_Amenities_' . date('dmY', strtotime($startDate)) . '-' . date('dmY', strtotime($endDate)) . '.pdf';
    
            // 4. Download
            return $pdf->download($namaFile);

        } catch (\Exception $e) {
            // --- PERBAIKAN 2: Tangkap Error jika PDF Gagal Digenerate ---
            // Ini akan memberi tahu Anda kenapa PDF gagal (misal: gambar not found di blade)
            return redirect()->back()->with('failed', 'Gagal cetak PDF. Error: ' . $e->getMessage());
        }
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOpnameAmenity; // Pastikan Model ini ada dan sesuai
use Barryvdh\DomPDF\Facade\Pdf;    // Library DomPDF

class LaporanStockopnameAmenities extends Controller
{
    /**
     * Fungsi untuk mencetak Laporan PDF berdasarkan range tanggal
     */
    public function exportPdf(Request $request)
    {
        // 1. Ambil input tanggal dari Form Modal
        // Kalau user tidak pilih tanggal, defaultnya ambil data bulan ini
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate   = $request->input('end_date', date('Y-m-d'));

        // 2. Ambil data dari database (StockOpnameAmenity)
        $data = StockOpnameAmenity::with('amenity') // Pastikan relasi 'amenity' ada di Model
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc') // Urutkan dari yang paling baru
                ->get();

        // 3. Load View PDF dan kirim datanya
        // Pastikan file view 'resources/views/reports/pdf_stok_amenities.blade.php' sudah dibuat
        $pdf = Pdf::loadView('reports.pdf_stok_amenities', [
            'data'      => $data,
            'startDate' => $startDate,
            'endDate'   => $endDate
        ]);

        // (Opsional) Set ukuran kertas, misal A4 Portrait
        $pdf->setPaper('a4', 'portrait');

        // 4. Download File PDF
        // Nama file otomatis: Laporan_Opname_Amenities_TGLAWAL-TGLAKHIR.pdf
        $namaFile = 'Laporan_Opname_Amenities_' . date('dmY', strtotime($startDate)) . '-' . date('dmY', strtotime($endDate)) . '.pdf';

       
        return $pdf->download($namaFile);
    }
}
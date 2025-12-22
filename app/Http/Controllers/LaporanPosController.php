<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanPosRepositoryInterface;

class LaporanPosController extends Controller
{
    public function __construct(
        private LaporanPosRepositoryInterface $laporanPosRepository
    ) {}

    /**
     * Halaman Utama Laporan Kasir
     * Ajax: Return DataTables JSON
     * Non-Ajax: Return Blade View
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->laporanPosRepository->getLaporanPosDatatable($request);
        }

        return view('laporan.kasir.index'); 
    }

    /**
     * Export CSV Manual (PHP Native - Tanpa Library)
     * Format yang sama dengan laporan rapat
     */
    public function exportExcel(Request $request)
    {
        // 1. Ambil data dari Repository (sudah terfilter tanggal)
        $query = $this->laporanPosRepository->getLaporanPosQuery($request);
        $transactions = $query->get();

        // 2. Tentukan Header CSV dengan nama file
        $fileName = 'laporan_kasir_' . date('d-m-Y_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 3. Buat fungsi callback untuk streaming data
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM untuk encoding UTF-8 (agar Excel tidak error)
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Tulis Judul Kolom
            fputcsv($file, [
                'No Invoice', 
                'Tanggal Transaksi', 
                'Jam', 
                'Metode Pembayaran', 
                'Menu Terjual (Qty)', 
                'Total Tagihan (Rp)', 
                'Dibayar (Rp)', 
                'Kembalian (Rp)'
            ]);

            // Tulis Data Transaksi per Baris
            foreach ($transactions as $row) {
                // Format list menu yang dibeli
                $itemList = $row->details->map(function($detail) {
                    return $detail->menu->name . ' (' . $detail->qty . ')';
                })->implode('; '); 

                $data = [
                    // Tambahkan apostrophe (') agar Excel mengenali sebagai teks
                    "'" . $row->invoice_number, 
                    $row->created_at->format('d-m-Y'),
                    $row->created_at->format('H:i'),
                    $row->payment_method,
                    $itemList,
                    // Format angka dengan pemisah ribuan
                    number_format($row->total_amount, 0, ',', '.'), 
                    number_format($row->pay_amount, 0, ',', '.'),
                    number_format($row->change_amount, 0, ',', '.')
                ];

                fputcsv($file, $data);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
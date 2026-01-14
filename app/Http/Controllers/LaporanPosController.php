<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanPosRepositoryInterface;
use App\Models\TransactionPos;
use Barryvdh\DomPDF\Facade\Pdf;


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
        // 1. Validasi Input
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        // 2. Query Data
        $transactions = TransactionPos::with(['details.menu'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Nama File
        $fileName = 'Laporan_Kasir_' . date('d-m-Y_His') . '.xls';

        // 4. Header agar browser menganggap ini file Excel
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // 5. Return View Blade yang tadi kita buat
        // Pastikan nama view sesuai dengan lokasi file kamu
        return view('laporan.kasir.excel', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    public function exportPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Ambil data transaksi kasir dengan relasi
        $transactions = TransactionPos::with(['details.menu'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total omset
        $totalOmset = $transactions->sum('total_amount');

        // Data untuk PDF
        $data = [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalOmset' => $totalOmset,
            'printDate' => now()->format('d/m/Y H:i'),
            'totalTransaksi' => $transactions->count(),
        ];

        // Load view PDF
        $pdf = PDF::loadView('laporan.kasir.pdf', $data);
        
        // Set paper size dan orientation (landscape karena banyak kolom)
        $pdf->setPaper('a4', 'landscape');
        
        // Download PDF dengan nama file dinamis
        $filename = 'Laporan_Kasir_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
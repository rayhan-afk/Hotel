<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanRepositoryInterface;

// MENGHAPUS SEMUA REFERENSI KE MAATWEBSITE\EXCEL

class LaporanController extends Controller
{
    public function __construct(
        private LaporanRepositoryInterface $laporanRepository
    ) {}

    public function laporanRuangRapat(Request $request)
    {
        if ($request->ajax()) {
            return $this->laporanRepository->getLaporanRapatDatatable($request);
        }

        return view('laporan.rapat.index');
    }

    /**
     * Export CSV Manual (PHP Native)
     * Ini menjamin fitur Export berfungsi tanpa error library.
     */
   /**
     * Export Excel Rapat (Metode View Blade)
     */
    public function exportExcel(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        // 2. Ambil Query dari Repository
        $query = $this->laporanRepository->getLaporanRapatQuery($request);
        
        // Eksekusi Query
        // Penting: Tambahkan ->with(['rapatCustomer']) untuk performa (Eager Loading)
        $transactions = $query->with(['rapatCustomer'])
                              ->orderBy('tanggal_pemakaian', 'desc')
                              ->get();

        // 3. Nama File
        $fileName = 'Laporan_Rapat_' . date('d-m-Y_H-i') . '.xls';

        // 4. Header Browsers (Agar dianggap file Excel)
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // 5. Return View Blade
        return view('laporan.rapat.excel', [
            'transactions' => $transactions
        ]);
    }

    public function laporanKamarHotel(Request $request)
    {
        return redirect()->route('dashboard.index')->with('info', 'Fitur belum tersedia.');
    }
}
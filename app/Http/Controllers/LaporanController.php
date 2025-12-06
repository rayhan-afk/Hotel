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
    public function exportExcel(Request $request)
    {
        // 1. Ambil data dari Repository (sudah terfilter tanggal)
        $query = $this->laporanRepository->getLaporanRapatQuery($request);
        $transactions = $query->get();

        // 2. Tentukan Header CSV (MEMBERIKAN NAMA FILE)
        $fileName = 'laporan_rapat_' . date('d-m-Y_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 3. Buat fungsi callback untuk streaming data
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan byte order mark (BOM) untuk encoding UTF-8 agar Excel tidak error saat buka CSV
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); 

            // Tulis Judul Kolom
            fputcsv($file, [
                'No Transaksi', 'Instansi / Perusahaan', 'Nama Pemesan', 'No Handphone', 
                'Email', 'Tanggal Rapat', 'Jam Mulai', 'Jam Selesai', 
                'Jumlah Peserta', 'Total Tagihan (Rp)', 'Status Pembayaran', 'Status Reservasi'
            ]);

            // Tulis Data Transaksi per Baris
            foreach ($transactions as $row) {
                // Tentukan data row
                $data = [
                    '#' . $row->id,
                    $row->rapatCustomer->instansi ?? '-',
                    $row->rapatCustomer->nama,
                    // PERBAIKAN NO HP: Tambahkan apostrophe (') agar menjadi teks
                    "'" . $row->rapatCustomer->no_hp,
                    "'" . $row->rapatCustomer->email, 
                    \App\Helpers\Helper::dateFormat($row->tanggal_pemakaian),
                    $row->waktu_mulai,
                    $row->waktu_selesai,
                    $row->jumlah_peserta,
                    // PERBAIKAN TOTAL BAYAR: Format angka penuh dengan pemisah ribuan
                    number_format($row->total_pembayaran, 0, ',', '.'),
                    $row->status_pembayaran,
                    $row->status_reservasi,
                ];
                fputcsv($file, $data);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function laporanKamarHotel(Request $request)
    {
        return redirect()->route('dashboard.index')->with('info', 'Fitur belum tersedia.');
    }
}
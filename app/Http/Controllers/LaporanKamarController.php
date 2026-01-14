<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan library DomPDF sudah terinstall

class LaporanKamarController extends Controller
{
    public function __construct(
        private LaporanKamarRepositoryInterface $laporanKamarRepository
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->laporanKamarRepository->getLaporanKamarDatatable($request);
        }
        return view('laporan.kamar.index');
    }

    public function exportExcel(Request $request)
{
    // 1. Ambil Query Data (Gunakan Repository yang sudah ada)
    $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
    
    // Pastikan memuat relasi yang dibutuhkan untuk efisiensi (Eager Loading)
    $transactions = $query->with(['customer.user', 'room.type'])->get();

    // 2. Setup Header Response
    $fileName = 'Laporan_Kamar_' . date('d-m-Y_H-i') . '.csv';
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8', // Pastikan charset UTF-8
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0',
    ];

    // 3. Callback Streaming Data
    $callback = function() use ($transactions) {
        $file = fopen('php://output', 'w');
        
        // Tambahkan BOM (Byte Order Mark) agar Excel membaca karakter UTF-8 dengan benar (termasuk Rupiah/Simbol)
        fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

        // HEADER KOLOM (Disusun Rapi)
        fputcsv($file, [
            // A. INFORMASI DASAR
            'No', 
            'ID Transaksi', 
            'Status',

            // B. INFORMASI TAMU
            'Nama Tamu', 
            'Email', 
            'No HP', 
            'Jenis Kelamin', 
            'Pekerjaan', 
            'Alamat',

            // C. DETAIL KAMAR
            'Nomor Kamar', 
            'Tipe Kamar',
            'Sarapan',

            // D. WAKTU & DURASI
            'Waktu Check In (Real)', 
            'Waktu Check Out (Real)', 
            'Rencana Check Out',
            'Durasi (Malam)', 
            'Catatan Waktu (Early/Late)', // Kolom Analisa Waktu

            // E. KEUANGAN
            'Harga Kamar/Malam',
            'Total Tagihan (Rp)' 
        ]);

        // ISI DATA PER BARIS
        foreach ($transactions as $index => $t) {
            
            // --- 1. Persiapan Data Harga & Durasi ---
            $totalHarga = $t->total_price ?? 0;
            $roomPrice  = $t->room->price ?? 0;
            
            // Hitung durasi estimasi (jika data durasi tidak tersimpan eksplisit)
            // Rumus: Total Harga / Harga Kamar (pembulatan) -> Minimal 1
            $durasiPaket = ($roomPrice > 0) ? round($totalHarga / $roomPrice) : 1;
            if ($durasiPaket < 1) $durasiPaket = 1;

            // --- 2. Persiapan Data Waktu ---
            $realCheckIn  = \Carbon\Carbon::parse($t->check_in);
            // Gunakan 'updated_at' jika status sudah selesai (checkout real), atau null jika belum
            $isCheckout   = in_array($t->status, ['Done', 'Payment Done', 'Selesai']);
            $realCheckOut = ($isCheckout && $t->updated_at) ? \Carbon\Carbon::parse($t->updated_at) : null;
            
            // Rencana Check Out = Check In + Durasi
            $planCheckOut = $realCheckIn->copy()->addDays($durasiPaket);

            // --- 3. Logika Analisa Waktu (Early/Late) ---
            $notes = [];

            // A. Early Check-in (Masuk sebelum jam 14:00)
            if ($realCheckIn->format('H') < 14) {
                $notes[] = 'Early Check-in (' . $realCheckIn->format('H:i') . ')';
            }

            if ($realCheckOut) {
                // B. Late Check-out (Keluar setelah jam 12:30 - toleransi 30 menit)
                if ($realCheckOut->format('H') > 12 || ($realCheckOut->format('H') == 12 && $realCheckOut->format('i') > 30)) {
                    $notes[] = 'Late Check-out (' . $realCheckOut->format('H:i') . ')';
                }

                // C. Pulang Lebih Awal (Tanggal keluar < Rencana)
                if ($realCheckOut->startOfDay()->lt($planCheckOut->copy()->startOfDay())) {
                    $notes[] = 'Pulang Lebih Awal';
                }
            } else {
                $notes[] = 'Belum Checkout';
            }
            
            $catatanWaktu = empty($notes) ? '-' : implode(', ', $notes);

            // --- 4. Format Data Lain ---
            $hp = $t->customer->phone ?? '-';
            // Tambahkan tanda kutip satu (') di depan No HP agar Excel tidak mengubahnya jadi format ilmiah (misal: 6.28E+10)
            $hpFormatted = ($hp !== '-') ? "'" . $hp : '-'; 

            // --- 5. Susun Array Data ---
            $data = [
                // A
                $index + 1,
                '#' . $t->id,
                $t->status,

                // B
                $t->customer->name ?? 'Guest',
                $t->customer->user->email ?? '-',
                $hpFormatted,
                $t->customer->gender ?? '-',
                $t->customer->job ?? '-',
                $t->customer->address ?? '-',

                // C
                $t->room->number ?? '-',
                $t->room->type->name ?? '-',
                ($t->breakfast == 'Yes' || $t->breakfast == 1) ? 'Yes' : 'No',

                // D
                $realCheckIn->format('d/m/Y H:i'),
                $realCheckOut ? $realCheckOut->format('d/m/Y H:i') : '-',
                $planCheckOut->format('d/m/Y'),
                $durasiPaket,
                $catatanWaktu,

                // E
                $roomPrice,  // Harga Satuan
                $totalHarga  // Total Tagihan
            ];
            
            fputcsv($file, $data);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

        public function downloadPdf(Request $request)
    {
        // 1. Ambil Query
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        
        // Ambil Data
        $transactions = $query->with(['customer.user', 'room.type'])
                            ->orderBy('check_in', 'desc')
                            ->limit(300) 
                            ->get();

        // 2. Siapkan Data untuk View (Termasuk start/end date untuk Judul)
        $data = [
            'title'        => 'Laporan Riwayat Kamar',
            'date'         => \Carbon\Carbon::now()->format('d F Y H:i'),
            'transactions' => $transactions,
            'start_date'   => $request->start_date, // Kirim ke View
            'end_date'     => $request->end_date,   // Kirim ke View
        ];

        // 3. Load View PDF
        $pdf = Pdf::loadView('laporan.kamar.pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        // 4. Generate Nama File Dinamis
        $filename = 'Laporan_Riwayat_Kamar';

        if ($request->start_date && $request->end_date) {
            // Format: d-m-Y (Tanggal-Bulan-Tahun)
            $tglAwal  = date('d-m-Y', strtotime($request->start_date));
            $tglAkhir = date('d-m-Y', strtotime($request->end_date));
            $filename .= '_' . $tglAwal . '_sd_' . $tglAkhir;
        } else {
            $filename .= '_Semua_Waktu_' . date('d-m-Y');
        }

        $filename .= '.pdf';

        // 5. Stream Download
        return $pdf->stream($filename);
    }
}
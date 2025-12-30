<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use Carbon\Carbon;

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
        // 1. Ambil Query
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        $transactions = $query->with(['customer.user', 'room.type'])->get();

        // 2. Setup Header
        $fileName = 'Laporan_Kamar_' . date('d-m-Y_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 3. Callback Streaming
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

            // HEADER KOLOM
            fputcsv($file, [
                'No', 
                'ID Transaksi', 
                'Nama Tamu', 
                'Nomor Kamar', 
                'Tipe Kamar',
                
                // DATA WAKTU
                'Waktu Check In', 
                'Waktu Check Out', 
                'Durasi Paket (Malam)', 
                'Rencana Check Out',
                
                // [KOLOM BARU] LOGIKA WAKTU (EARLY/LATE)
                'Catatan Waktu', 
                
                'Sarapan', 
                'Total Harga (Rp)', 
                'Status',
                
                // DATA TAMBAHAN
                'Email', 'No HP', 'Jenis Kelamin', 'Pekerjaan', 'Alamat'
            ]);

            // ISI DATA
            foreach ($transactions as $index => $t) {
                $totalHarga = $t->total_price ?? 0;
                
                // Hitung Durasi Paket
                $roomPrice = $t->room->price ?? 1;
                if($roomPrice <= 0) $roomPrice = 1;
                $durasiPaket = round($totalHarga / $roomPrice);
                if($durasiPaket < 1) $durasiPaket = 1;
                
                // Waktu Real
                $realCheckIn = Carbon::parse($t->check_in);
                $realCheckOut = $t->check_out ? Carbon::parse($t->check_out) : null;
                $planCheckOut = $realCheckIn->copy()->addDays($durasiPaket);
                
                // --- LOGIKA DETEKSI JAM (EARLY/LATE) ---
                $notes = [];

                // 1. Early Check-in (Sebelum jam 14:00)
                if ($realCheckIn->format('H') < 14) {
                    $notes[] = 'Early Check-in (' . $realCheckIn->format('H:i') . ')';
                }

                if ($realCheckOut) {
                    // 2. Late Check-out (Setelah jam 12:00)
                    // Menggunakan >= 12 artinya jam 12:00 pas atau lebih (misal 13:00) dianggap late
                    if ($realCheckOut->format('H') >= 12) {
                        $notes[] = 'Late Check-out (' . $realCheckOut->format('H:i') . ')';
                    }

                    // 3. Pulang Awal (Beda Hari)
                    // Jika tanggal keluar < tanggal rencana
                    if ($realCheckOut->startOfDay()->lt($planCheckOut->copy()->startOfDay())) {
                        $notes[] = 'Pulang Lebih Awal (Sisa Hari)';
                    }
                } else {
                    $notes[] = 'Belum Checkout';
                }

                $catatanWaktu = empty($notes) ? '-' : implode(', ', $notes);
                // ------------------------------------------

                // Format No HP
                $hpRaw = $t->customer->phone ?? '-';
                $hp = $hpRaw !== '-' ? "'" . $hpRaw : '-';

                $data = [
                    $index + 1,
                    '#' . $t->id,
                    $t->customer->name ?? 'Guest',
                    $t->room->number ?? '-',
                    $t->room->type->name ?? '-',
                    
                    $realCheckIn->format('d/m/Y H:i'),
                    $realCheckOut ? $realCheckOut->format('d/m/Y H:i') : '-',
                    $durasiPaket,
                    $planCheckOut->format('d/m/Y'),
                    
                    $catatanWaktu, // [Hasil Logika]
                    
                    ($t->breakfast == 'Yes' || $t->breakfast == 1) ? 'Yes' : 'No',
                    $totalHarga,
                    $t->status,
                    
                    $t->customer->user->email ?? '-',
                    $hp,
                    $t->customer->gender ?? '-',
                    $t->customer->job ?? '-',
                    $t->customer->address ?? '-',
                ];
                
                fputcsv($file, $data);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
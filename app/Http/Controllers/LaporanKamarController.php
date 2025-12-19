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
        // 1. Handle Request AJAX (DataTables)
        if ($request->ajax()) {
            return $this->laporanKamarRepository->getLaporanKamarDatatable($request);
        }

        // 2. Handle Request Biasa (View Halaman)
        // Kita paginate manual query dari repository untuk tampilan awal
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        $transactions = $query->paginate(10)->appends($request->all());

        return view('laporan.kamar.index', compact('transactions'));
    }

    public function exportExcel(Request $request)
    {
        // 1. Ambil Query dari Repository
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        
        // [OPTIMASI] Tidak perlu ->with() lagi karena di Repository sudah ada.
        // Langsung eksekusi ->get()
        $transactions = $query->get();

        // 2. Setup Header CSV
        $fileName = 'Laporan_Lengkap_Reservasi_' . date('d-m-Y_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 3. Callback Streaming (Hemat Memori Server)
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM (Byte Order Mark) agar Excel bisa baca simbol/rupiah dengan benar
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

            // === HEADER KOLOM ===
            fputcsv($file, [
                'No', 'ID Transaksi', 
                'Nama Tamu', 'Email', 'No HP', 'Jenis Kelamin', 
                'Tanggal Lahir', 'Pekerjaan', 'Alamat',
                'Nomor Kamar', 'Tipe Kamar', 
                'Check In', 'Check Out', 'Sarapan', 
                'Total Harga (Rp)', 'Status'
            ]);

            // === ISI DATA ===
            foreach ($transactions as $index => $t) {
                // Prioritaskan Harga Database
                $totalHarga = $t->total_price ?? $t->getTotalPrice();
                
                // Format Tanggal
                $checkIn = Carbon::parse($t->check_in)->format('d-m-Y');
                $checkOut = Carbon::parse($t->check_out)->format('d-m-Y');
                $birthdate = !empty($t->customer->birthdate) ? Carbon::parse($t->customer->birthdate)->format('d-m-Y') : '-';

                // Format Gender
                $genderRaw = $t->customer->gender ?? '-';
                $gender = ($genderRaw == 'Male') ? 'Laki-laki' : (($genderRaw == 'Female') ? 'Perempuan' : '-');

                // Format Breakfast
                $breakfast = ($t->breakfast == 'Yes' || $t->breakfast == 1) ? 'Yes' : 'No';

                // Format No HP (Trik Excel)
                $hpRaw = $t->customer->phone ?? '-';
                $hp = $hpRaw !== '-' ? "'" . $hpRaw : '-';

                // Mapping Data Baris
                $data = [
                    $index + 1,
                    '#' . $t->id,
                    $t->customer->name ?? 'Guest',
                    $t->customer->user->email ?? '-',
                    $hp,
                    $gender,
                    $birthdate,
                    $t->customer->job ?? '-',
                    $t->customer->address ?? '-',
                    $t->room->number ?? '-',
                    $t->room->type->name ?? '-',
                    $checkIn,
                    $checkOut,
                    $breakfast,
                    $totalHarga, // Biarkan angka mentah agar bisa dijumlah di Excel
                    $t->status
                ];
                
                fputcsv($file, $data);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use Carbon\Carbon;

class LaporanKamarController extends Controller
{
    // Inject Repository lewat Constructor
    public function __construct(
        private LaporanKamarRepositoryInterface $laporanKamarRepository
    ) {}

    public function index(Request $request)
    {
        // Jika request AJAX (dari DataTables)
        if ($request->ajax()) {
            return $this->laporanKamarRepository->getLaporanKamarDatatable($request);
        }

        // Jika request biasa (View Halaman Awal)
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        $transactions = $query->paginate(10)->appends($request->all());

        return view('laporan.kamar.index', compact('transactions'));
    }

    public function exportExcel(Request $request)
    {
        // 1. Ambil Data dari Repository
        $query = $this->laporanKamarRepository->getLaporanKamarQuery($request);
        
        // Eager load relasi customer dan user untuk performa & kelengkapan data
        $transactions = $query->with(['customer.user', 'room.type'])->get();

        // 2. Setup Header CSV
        $fileName = 'Laporan_Lengkap_Reservasi_' . date('d-m-Y_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // 3. Callback Streaming
        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM agar karakter khusus/simbol terbaca aman di Excel
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

            // === HEADER KOLOM LENGKAP ===
            fputcsv($file, [
                'No',
                'ID Transaksi', 
                
                // DATA CUSTOMER LENGKAP
                'Nama Tamu', 
                'Email',
                'No HP',
                'Jenis Kelamin',   // <-- Data Gender
                'Tanggal Lahir',   
                'Pekerjaan',
                'Alamat',
                
                // DATA RESERVASI
                'Nomor Kamar', 
                'Tipe Kamar', 
                'Check In', 
                'Check Out', 
                'Sarapan', 
                'Total Harga (Rp)', 
                'Status'
            ]);

            // === ISI DATA ===
            foreach ($transactions as $index => $t) {
                // Harga
                $totalHarga = $t->total_price ?? $t->getTotalPrice();
                
                // Format Tanggal Checkin/out
                $checkIn = Carbon::parse($t->check_in)->format('d-m-Y');
                $checkOut = Carbon::parse($t->check_out)->format('d-m-Y');

                // Logic Sarapan
                $breakfast = ($t->breakfast == 'Yes' || $t->breakfast == 1) ? 'Yes' : 'No';

                // Data Customer (Null Safe Operator ??)
                $nama = $t->customer->name ?? 'Guest';
                $email = $t->customer->user->email ?? '-'; 
                
                // Format No HP (tambah kutip biar 0 aman)
                $hpRaw = $t->customer->phone ?? '-';
                $hp = $hpRaw !== '-' ? "'" . $hpRaw : '-';

                // [PERBAIKAN] Format Gender ke Bahasa Indonesia
                $genderRaw = $t->customer->gender ?? '-';
                $gender = '-';
                if ($genderRaw == 'Male') {
                    $gender = 'Laki-laki';
                } elseif ($genderRaw == 'Female') {
                    $gender = 'Perempuan';
                }

                // Format Tanggal Lahir
                $birthdate = '-';
                if (!empty($t->customer->birthdate)) {
                    $birthdate = Carbon::parse($t->customer->birthdate)->format('d-m-Y');
                }

                $job = $t->customer->job ?? '-';
                $alamat = $t->customer->address ?? '-';

                $data = [
                    $index + 1,
                    '#' . $t->id,
                    
                    // Detail Customer
                    $nama,
                    $email,
                    $hp, 
                    $gender, // <-- Pakai variabel gender yang sudah diterjemahkan
                    $birthdate,
                    $job,
                    $alamat,

                    // Detail Kamar
                    $t->room->number ?? '-',
                    $t->room->type->name ?? '-',
                    $checkIn, 
                    $checkOut,
                    $breakfast,
                    $totalHarga, 
                    $t->status
                ];
                
                fputcsv($file, $data);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
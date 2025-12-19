<?php

namespace App\Repositories\Implementation;

use App\Helpers\Helper;
use App\Models\Transaction;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanKamarRepository implements LaporanKamarRepositoryInterface
{
    /**
     * Query Dasar: Digunakan bersama oleh DataTables DAN Export Excel.
     */
    public function getLaporanKamarQuery($request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // --- LOGIKA SEARCH ---
        $searchDatatableValue = $request->input('search.value');
        $searchUrlValue = $request->input('search');
        $search = $searchDatatableValue ?: $searchUrlValue;
        
        if (is_array($search)) {
            $search = $search['value'] ?? null;
        }
        // ---------------------

        $query = Transaction::select('transactions.*') // PENTING: Ambil semua kolom transaksi (termasuk total_price)
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('types', 'rooms.type_id', '=', 'types.id')
            ->with(['customer.user', 'room.type']);

        // === FILTER STATUS ===
        // Hanya tampilkan data histori (Selesai/Lunas/Checkout)
        // Jangan tampilkan yang masih aktif (Reservation/Check In)
        $query->whereNotIn('transactions.status', ['Reservation', 'Check In', 'Cleaning']);

        // Filter Tanggal
        if ($startDate) {
            $query->whereDate('transactions.check_in', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transactions.check_in', '<=', $endDate);
        }

        // Filter Search Global
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%")
                  ->orWhere('transactions.id', 'LIKE', "%{$search}%"); // Tambah cari by ID
            });
        }
        
        // Default Order: Data terbaru (checkout terakhir) paling atas
        $query->orderBy('transactions.updated_at', 'DESC');

        return $query;
    }

    public function saveToLaporan($t)
    {
        // Method legacy, biarkan saja
    }

    /**
     * Khusus DataTables
     */
    public function getLaporanKamarDatatable($request)
    {
        $query = $this->getLaporanKamarQuery($request); 

        // Kolom untuk Sorting
        $columns = [
            0 => 'customers.name',
            1 => 'rooms.number',
            2 => 'transactions.check_in',
            3 => 'transactions.check_out',
            4 => 'transactions.breakfast',
            5 => 'transactions.total_price', 
            6 => 'transactions.status',
            // 7 => Aksi
        ];

        // Hitung Total Data
        $totalData = Transaction::whereNotIn('status', ['Reservation', 'Check In', 'Cleaning'])->count();
        $totalFiltered = $query->count(); 

        // Pagination
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 3); // Default sort by Check Out
        $orderDir = $request->input('order.0.dir', 'desc');

        $orderBy = $columns[$orderColumnIndex] ?? 'transactions.updated_at';

        // Validasi order by column agar tidak error
        $query->orderBy($orderBy, $orderDir);
        
        if ($limit != -1) {
            $query->offset($start)->limit($limit);
        }

        $models = $query->get();

        $data = [];
        foreach ($models as $model) {
            // 1. AMBIL HARGA DARI DATABASE (Safe History)
            // Prioritaskan kolom 'total_price' di DB.
            $totalHarga = $model->total_price; 
            
            // Jika data lama kosong, baru fallback ke helper
            if (!$totalHarga) {
                $totalHarga = $model->getTotalPrice(); 
            }

            // 2. STATUS LABEL BADGE
            $statusLabel = $model->status;
            if ($model->status == 'Done') {
                $statusLabel = '<span class="badge bg-success shadow-sm">Selesai</span>';
            } elseif ($model->status == 'Paid') {
                $statusLabel = '<span class="badge bg-primary shadow-sm">Lunas</span>';
            } elseif ($model->status == 'Cancel') {
                $statusLabel = '<span class="badge bg-danger shadow-sm">Dibatalkan</span>';
            } else {
                $statusLabel = '<span class="badge bg-secondary">'.$model->status.'</span>';
            }

            // === [FIX UTAMA] INVOICE LINK KE STATIC HISTORY ===
            // Jangan pakai 'previewInvoice' lagi.
            // Gunakan route baru 'transaction.invoice.print' yang mengambil data DB.
            $invoiceUrl = route('transaction.invoice.print', ['transaction' => $model->id]);

            $btnAction = '
                <a href="'.$invoiceUrl.'" target="_blank" 
                   class="btn btn-sm btn-outline-primary shadow-sm fw-bold" 
                   title="Cetak Invoice">
                    <i class="fas fa-print me-1"></i> Invoice
                </a>
            ';

            $data[] = [
                'tamu'      => $model->customer->name,
                'kamar'     => '<strong>' . $model->room->number . '</strong> <span class="text-muted small">(' . ($model->room->type->name ?? '-') . ')</span>',
                'check_in'  => Helper::dateFormat($model->check_in),
                'check_out' => Helper::dateFormat($model->check_out),
                'sarapan'   => $model->breakfast,
                
                // Kirim float agar JS bisa format Rupiah
                'total_harga' => (float) $totalHarga, 
                
                'status'    => $statusLabel,
                'aksi'      => $btnAction 
            ];
        }

        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data, 
        ];
    }
}
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

        $query = Transaction::select('transactions.*')
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('types', 'rooms.type_id', '=', 'types.id')
            ->with(['customer.user', 'room.type']);

        // === LOGIC BARU: FILTER STATUS ===
        // Filter ini MENCEGAH Reservasi baru, Tamu Check-in, dan Kamar Cleaning muncul di laporan.
        // Data baru muncul di sini hanya jika statusnya sudah 'Done' (via Scheduler 1 jam)
        // atau 'Checked Out' / 'Paid' (Data lama).
        $query->whereNotIn('transactions.status', ['Reservation', 'Check In', 'Cleaning']);

        // Filter Tanggal
        if ($startDate) {
            $query->where('transactions.check_in', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('transactions.check_in', '<=', $endDate);
        }

        // Filter Search Global
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%");
            });
        }
        
        // Default Order: Data terbaru (checkout terakhir) paling atas
        $query->orderBy('transactions.updated_at', 'DESC');

        return $query;
    }

    public function saveToLaporan($t)
    {
        // Method legacy, biarkan saja jika tidak dipakai
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
            // 7 => Aksi (Tidak perlu sorting)
        ];

        // Hitung Total Data (Sesuai Filter Status di atas)
        $totalData = Transaction::whereNotIn('status', ['Reservation', 'Check In', 'Cleaning'])->count();
        $totalFiltered = $query->count(); 

        // Pagination
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir') ?? 'desc';

        $orderBy = $columns[$orderColumnIndex] ?? 'transactions.updated_at'; // Default sort update terakhir

        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        // Validasi order by column agar tidak error
        $query->orderBy($orderBy, $orderDir);

        $models = $query->get();

        $data = [];
        foreach ($models as $model) {
            // Hitung Total Harga
            $totalHarga = $model->total_price ?? $model->getTotalPrice();

            // [UPDATE] Tentukan Label Status dengan Badge HTML
            $statusLabel = $model->status;
            if ($model->status == 'Done') {
                $statusLabel = '<span class="badge bg-success">Selesai</span>';
            } elseif ($model->status == 'Paid') {
                $statusLabel = '<span class="badge bg-primary">Lunas</span>';
            } else {
                $statusLabel = '<span class="badge bg-secondary">'.$model->status.'</span>';
            }

            // === [LOGIKA BARU] URL INVOICE & TOMBOL AKSI ===
            // Format tanggal Y-m-d agar bersih di URL
            $checkInRaw = Carbon::parse($model->check_in)->format('Y-m-d');
            $checkOutRaw = Carbon::parse($model->check_out)->format('Y-m-d');

            // Generate URL ke route previewInvoice
            $invoiceUrl = route('transaction.reservation.previewInvoice', [
                'customer' => $model->customer_id,
                'room' => $model->room_id,
                'from' => $checkInRaw,
                'to' => $checkOutRaw
            ]) . '?breakfast=' . $model->breakfast;

            // Buat Tombol HTML
            $btnAction = '
                <a href="'.$invoiceUrl.'" target="_blank" 
                   class="btn btn-sm btn-light border shadow-sm" 
                   style="color: #50200C; font-weight: 600; background-color: #8FB8E1;" 
                   title="Lihat & Download Invoice">
                    <i class="fas fa-file-invoice me-1"></i> Invoice
                </a>
            ';

            $data[] = [
                'tamu' => $model->customer->name,
                'kamar' => 'Room ' . $model->room->number . ' (' . ($model->room->type->name ?? '-') . ')',
                'check_in' => Helper::dateFormat($model->check_in),
                'check_out' => Helper::dateFormat($model->check_out),
                'sarapan' => $model->breakfast,
                
                // Kirim ANGKA MENTAH (float) untuk diformat oleh JS
                'total_harga' => (float) $totalHarga, 
                
                'status' => $statusLabel, // Mengirim HTML Badge
                'aksi' => $btnAction // [BARU] Kolom Aksi
            ];
        }

        return [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data, // Gunakan 'data' sesuai standar DataTables terbaru
        ];
    }
}
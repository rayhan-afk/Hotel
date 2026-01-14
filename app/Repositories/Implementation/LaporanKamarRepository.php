<?php

namespace App\Repositories\Implementation;

use App\Helpers\Helper;
use App\Models\Transaction;
use App\Repositories\Interface\LaporanKamarRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanKamarRepository implements LaporanKamarRepositoryInterface
{
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

        $query = Transaction::select('transactions.*') 
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('types', 'rooms.type_id', '=', 'types.id')
            ->with(['customer.user', 'room.type']);

        // Filter Status Histori (Hanya tampilkan yang sudah selesai / Done / Cancelled)
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
                  ->orWhere('transactions.id', 'LIKE', "%{$search}%");
            });
        }
        
        return $query;
    }

    public function saveToLaporan($t)
    {
        // Method legacy
    }

    public function getLaporanKamarDatatable($request)
    {
        $query = $this->getLaporanKamarQuery($request); 

        // Mapping Kolom untuk Sorting
        $columns = [
            0 => 'transactions.id', 
            1 => 'customers.name', 
            2 => 'transactions.check_in', // Mapping ke Paket Menginap (Check In Rencana)
            3 => 'transactions.check_in', // Mapping ke Masuk Real
            4 => 'transactions.updated_at', // Mapping ke Keluar Real (Biasanya pakai updated_at saat status Done)
            5 => 'transactions.total_price', 
            6 => 'transactions.status',
        ];

        // Hitung Total Data (Tanpa Filter)
        $totalData = Transaction::whereNotIn('status', ['Reservation', 'Check In', 'Cleaning'])->count();
        
        // Hitung Total Data (Dengan Filter Search/Date)
        $totalFiltered = $query->count(); 

        // Pagination & Sorting
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 3); 
        $orderDir = $request->input('order.0.dir', 'desc');

        $orderBy = $columns[$orderColumnIndex] ?? 'transactions.updated_at';
        $query->orderBy($orderBy, $orderDir);
        
        if ($limit != -1) {
            $query->offset($start)->limit($limit);
        }

        $models = $query->get();

        $data = [];
        foreach ($models as $model) {
            // Ambil total harga
            $totalHarga = $model->total_price; 
            if (!$totalHarga) {
                // Fallback jika null, hitung manual (walaupun harusnya sudah tersimpan di DB)
                $totalHarga = $model->room->price; // Atau logic lain
            }

            $statusRaw = $model->status;

            // Tombol Cetak Invoice
            $invoiceUrl = route('transaction.invoice.print', ['transaction' => $model->id]);
            $btnAction = '
                <a href="'.$invoiceUrl.'" target="_blank" 
                   class="btn btn-sm btn-outline-primary shadow-sm fw-bold" 
                   title="Cetak Invoice">
                   <i class="fas fa-print me-1"></i> Invoice
                </a>
            ';

            $data[] = [
                'id'            => $model->id,
                'customer_name' => $model->customer->name,
                
                // [DATA BARU: JUMLAH TAMU]
                'count_person'  => $model->count_person ?? 1,
                'count_child'   => $model->count_child ?? 0,

                'room'          => $model->room, // Object Room
                'room_price'    => (float) $model->room->price, 
                
                // Waktu Check In & Check Out (Rencana vs Real)
                'check_in'      => $model->check_in,   
                'check_out'     => $model->check_out,  
                'updated_at'    => $model->updated_at, // Waktu Checkout Real (saat status jadi Done)

                'breakfast'     => $model->breakfast,
                'total_price'   => (float) $totalHarga, 
                'status'        => $statusRaw, 
                'action'        => $btnAction 
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
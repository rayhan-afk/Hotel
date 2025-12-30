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

        // Filter Status Histori
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
        
        $query->orderBy('transactions.updated_at', 'DESC');

        return $query;
    }

    public function saveToLaporan($t)
    {
        // Method legacy
    }

    public function getLaporanKamarDatatable($request)
    {
        $query = $this->getLaporanKamarQuery($request); 

        $columns = [
            0 => 'transactions.id', 
            1 => 'customers.name', 
            2 => 'transactions.check_in', 
            3 => 'transactions.check_in', 
            4 => 'transactions.check_out', 
            5 => 'transactions.total_price', 
            6 => 'transactions.status',
        ];

        $totalData = Transaction::whereNotIn('status', ['Reservation', 'Check In', 'Cleaning'])->count();
        $totalFiltered = $query->count(); 

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
            $totalHarga = $model->total_price; 
            if (!$totalHarga) {
                $totalHarga = $model->getTotalPrice(); 
            }

            $statusRaw = $model->status;

            // === [DIPERBAIKI DISINI] ===
            // Saya kembalikan ke style asli: Outline Primary + Tulisan "Invoice"
            $invoiceUrl = route('transaction.invoice.print', ['transaction' => $model->id]);
            $btnAction = '
                <a href="'.$invoiceUrl.'" target="_blank" 
                   class="btn btn-sm btn-outline-primary shadow-sm fw-bold" 
                   title="Cetak Invoice">
                   <i class="fas fa-print me-1"></i> Invoice
                </a>
            ';
            // ===========================

            $data[] = [
                'id'            => $model->id,
                'customer_name' => $model->customer->name,
                'room'          => $model->room, 
                'room_price'    => (float) $model->room->price, 
                
                'check_in'      => $model->check_in, // Ini Waktu Real
                'check_out'     => $model->check_out,
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
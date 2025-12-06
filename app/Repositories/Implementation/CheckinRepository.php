<?php

namespace App\Repositories\Implementation;

use App\Helpers\Helper;
use App\Models\Transaction;
use App\Repositories\Interface\CheckinRepositoryInterface;
use Carbon\Carbon;

class CheckinRepository implements CheckinRepositoryInterface
{
    public function getCheckinDatatable($request)
    {
        // ATURAN NO 6: Menampilkan tamu yang statusnya 'Check In'
        $query = Transaction::with(['customer', 'room.type', 'user'])
            ->where('status', 'Check In'); 

        // Filter Search
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('room', function($r) use ($search) {
                    $r->where('number', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $totalData = $query->count();
        // Urutkan yang baru checkin paling atas
        $query->orderBy('updated_at', 'DESC'); 

        // Pagination
        $limit = $request->length ?? 10;
        $start = $request->start ?? 0;
        $data = $query->skip($start)->take($limit)->get();

        $formattedData = [];
        foreach ($data as $trx) {
            $formattedData[] = [
                'id' => $trx->id,
                'customer_name' => $trx->customer->name,
                'room_info' => [
                    'number' => $trx->room->number,
                    'type' => $trx->room->type->name
                ],
                'check_in' => Helper::dateFormat($trx->check_in),
                'check_out' => Helper::dateFormat($trx->check_out),
                'breakfast' => $trx->breakfast ? $trx->breakfast : 'No',
                'total_price' => (float) $trx->total_price,
                'status' => 'Check In',
                'action' => $trx->id // ID dikirim untuk tombol aksi (Edit/Checkout)
            ];
        }

        return [
            'draw' => intval($request->draw),
            'recordsTotal' => Transaction::where('status', 'Check In')->count(),
            'recordsFiltered' => $totalData,
            'data' => $formattedData
        ];
    }

    public function getTransaction($id) 
    { 
        return Transaction::findOrFail($id); 
    }

    public function update($request, $id) 
    { 
        $t = Transaction::findOrFail($id);
        $t->update($request->all());
        return $t;
    }

    public function delete($id) 
    { 
        Transaction::findOrFail($id)->delete(); 
    }

    public function store($request) 
    { 
        // Logic Store jika diperlukan di masa depan
    }

    /**
     * Method BARU: Menangani proses Check Out tamu.
     * Mengubah status transaksi jadi 'Done' dan set waktu checkout actual.
     */
    public function checkoutGuest($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // 1. Ubah Status Transaksi jadi Done (Selesai)
        $transaction->update([
            'status' => 'Done',
            'check_out' => Carbon::now() 
        ]);
        
        // 2. TAMBAHAN: Ubah Status Kamar jadi 'Cleaning' (Sedang Dibersihkan)
        // Pastikan relasi 'room' ada di model Transaction
        $transaction->room->update([
            'status' => 'Cleaning'
        ]);
        
        return $transaction;
    }
}
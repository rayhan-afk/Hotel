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
        // Tetap hanya menampilkan yang sedang 'Check In'
        $query = Transaction::with(['customer', 'room.type', 'user'])
            ->where('status', 'Check In'); 

        // Filter Search Global
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
        $query->orderBy('updated_at', 'DESC'); 

        $limit = $request->length ?? 10;
        $start = $request->start ?? 0;
        $data = $query->skip($start)->take($limit)->get();

        $formattedData = [];
        foreach ($data as $trx) {
            
            // === LOGIKA SISA BAYAR ===
            $totalPrice = (float) $trx->total_price;
            $paid = (float) ($trx->paid_amount ?? 0);
            
            $remaining = $totalPrice - $paid;
            $remainingDisp = ($remaining > 0) ? $remaining : 0;

            $formattedData[] = [
                'id' => $trx->id,
                'customer_name' => $trx->customer->name,
                'room_info' => [
                    'number' => $trx->room->number,
                    'type' => $trx->room->type->name
                ],
                
                // Kirim data mentah (DateTime) agar JavaScript bisa ambil Jam-nya.
                'check_in' => $trx->check_in,   
                'check_out' => $trx->check_out, 

                // [DIHAPUS] extra_bed dan extra_breakfast sudah dibuang dari sini
                
                'breakfast' => $trx->breakfast ?? 'No',
                'total_price' => $totalPrice,
                'remaining_payment' => (float) $remainingDisp, 
                'status' => 'Check In',
                'action' => $trx->id 
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
        return Transaction::findOrFail($id); 
    }

    public function delete($id) 
    { 
        Transaction::findOrFail($id)->delete(); 
    }

    public function store($request) 
    { 
        // Logic Store
    }

    /**
     * Method Menangani proses Check Out tamu.
     */
    public function checkoutGuest($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // === SOLUSI FINAL: JANGAN UBAH KOLOM CHECK_OUT ===
        // Biarkan 'check_out' tetap tanggal masa depan (Rencana).
        // Waktu keluar asli akan kita ambil dari 'updated_at' (Waktu status berubah jadi Done).

        $transaction->update([
            'status'      => 'Done', 
            // 'check_out' => ... (JANGAN DIUBAH SAMA SEKALI)
            'paid_amount' => $transaction->total_price 
        ]);
        
        $transaction->room->update([
            'status' => 'Cleaning'
        ]);
        
        return $transaction;
    }
}
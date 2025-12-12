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
            
            // === LOGIKA SISA BAYAR (REMAINING PAYMENT) ===
            // Ambil data paid_amount (default 0 jika belum diset)
            $paid = $trx->paid_amount ?? 0;
            
            // Hitung selisih: Total Tagihan - Uang Masuk
            $remaining = $trx->total_price - $paid;
            
            // Pastikan tidak negatif (jika ada kelebihan bayar/kembalian, anggap sisa hutang 0)
            if ($remaining < 0) {
                $remaining = 0;
            }
            // =============================================

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
                
                // [BARU] Kirim data sisa bayar ke JavaScript
                'remaining_payment' => (float) $remaining, 

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
        $t = Transaction::findOrFail($id);
        
        // Update data transaksi (Biasanya Extend Tanggal & Total Harga baru)
        // paid_amount TIDAK diupdate di sini, sehingga selisih harga akan muncul sebagai sisa bayar
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
     * Method Menangani proses Check Out tamu.
     */
    public function checkoutGuest($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // 1. Ubah Status Transaksi jadi Done (Selesai)
        // 2. Set waktu checkout actual
        // 3. [PENTING] Set paid_amount = total_price (Anggap pelunasan terjadi saat checkout)
        $transaction->update([
            'status' => 'Done',
            'check_out' => Carbon::now(),
            'paid_amount' => $transaction->total_price 
        ]);
        
        // 4. Ubah Status Kamar jadi 'Cleaning' (Sedang Dibersihkan)
        $transaction->room->update([
            'status' => 'Cleaning'
        ]);
        
        return $transaction;
    }
}
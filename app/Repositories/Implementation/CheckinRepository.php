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
            
            // === LOGIKA SISA BAYAR (KHUSUS UNTUK RESEPSIONIS) ===
            // total_price adalah harga setelah ditambah Extra-extra
            $totalPrice = (float) $trx->total_price;
            
            // paid_amount adalah uang yang sudah dibayar tamu di awal
            $paid = (float) ($trx->paid_amount ?? 0);
            
            // Selisih inilah yang harus ditagih resepsionis
            $remaining = $totalPrice - $paid;
            $remainingDisp = ($remaining > 0) ? $remaining : 0;

            $formattedData[] = [
                'id' => $trx->id,
                'customer_name' => $trx->customer->name,
                'room_info' => [
                    'number' => $trx->room->number,
                    'type' => $trx->room->type->name
                ],
                'check_in' => Helper::dateFormat($trx->check_in),
                'check_out' => Helper::dateFormat($trx->check_out),
                'extra_bed' => (int) $trx->extra_bed, 
                'extra_breakfast' => (int) $trx->extra_breakfast, 
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
        // Biarkan Controller yang melakukan update() 
        // Agar hitungan total_price hasil pajak & extra tidak tertimpa data mentah
        return Transaction::findOrFail($id); 
    }

    public function delete($id) 
    { 
        Transaction::findOrFail($id)->delete(); 
    }

    public function store($request) 
    { 
        // Logic Store jika diperlukan
    }

    /**
     * Method Menangani proses Check Out tamu.
     * Saat Checkout, kita anggap tamu sudah melunasi semuanya.
     */
    public function checkoutGuest($id)
    {
        $transaction = Transaction::findOrFail($id);
        
        $transaction->update([
            'status' => 'Done',
            
            // --- BAGIAN INI SAYA HAPUS ---
            // 'check_out' => Carbon::now(),  <-- BIANG KEROKNYA DI SINI
            // Kita hapus baris di atas, supaya tanggal checkout TETAP sesuai reservasi (misal tgl 27)
            // -----------------------------
            
            // Pelunasan otomatis (Opsional, sesuai request sebelumnya)
            'paid_amount' => $transaction->total_price 
        ]);
        
        // Ubah Status Kamar jadi 'Cleaning'
        $transaction->room->update([
            'status' => 'Cleaning'
        ]);
        
        return $transaction;
    }
}
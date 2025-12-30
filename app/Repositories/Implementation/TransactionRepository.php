<?php

namespace App\Repositories\Implementation;

use App\Models\Transaction;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function store($request, $customer, $room)
    {
        // 1. Ambil data checkin/checkout sekadar untuk disimpan
        $check_in = Carbon::parse($request->check_in);
        $check_out = Carbon::parse($request->check_out);

        // 2. SIMPAN TRANSAKSI
        return Transaction::create([
            'user_id'     => auth()->id(), 
            'customer_id' => $customer->id,
            'room_id'     => $room->id,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'status'      => 'Reservation', 
            
            'breakfast'   => $request->breakfast ?? 'No',
            
            // Simpan Total Tagihan
            'total_price' => $request->total_price,

            // [PERBAIKAN WAJIB DISINI]
            // Simpan Total Bayar sama persis dengan Total Tagihan.
            // Ini membuat status transaksi di database menjadi LUNAS (Sisa Bayar = 0).
            'paid_amount' => $request->total_price 
        ]);
    }

    public function getTransaction($request)
    {
        return Transaction::with('user', 'room', 'customer')
            ->where('check_out', '>=', Carbon::now())
            ->orderBy('check_in', 'ASC')
            ->paginate(10);
    }

    public function getTransactionExpired($request)
    {
        return Transaction::with('user', 'room', 'customer')
            ->where('check_out', '<', Carbon::now())
            ->orderBy('check_out', 'DESC')
            ->paginate(10);
    }
}
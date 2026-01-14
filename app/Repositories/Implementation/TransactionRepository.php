<?php

namespace App\Repositories\Implementation;

use App\Models\Transaction;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function store($request, $customer, $room)
    {
        // Mendefinisikan variabel Count (WAJIB ADA)
        $countPerson = $request->input('count_person', 1);
        $countChild  = $request->input('count_child', 0);

        // Jika Anda ingin tetap ada Carbon (boleh, tapi tidak wajib)
        $check_in = Carbon::parse($request->check_in);
        $check_out = Carbon::parse($request->check_out);

        return Transaction::create([
            'user_id'     => auth()->id(), 
            'customer_id' => $customer->id,
            'room_id'     => $room->id,
            
            // Kalau mau pakai Carbon yg diatas, di sini harus diganti jadi variabelnya
            'check_in'    => $check_in,   
            'check_out'   => $check_out,
            
            'status'      => 'Reservation', 
            'count_person'  => $countPerson,
            'count_child'   => $countChild,
            'breakfast'   => $request->breakfast ?? 'No',
            'total_price' => $request->total_price,
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
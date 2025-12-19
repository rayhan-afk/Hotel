<?php

namespace App\Repositories\Implementation;

use App\Models\Transaction;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function store($request, $customer, $room)
    {
        // 1. Ambil data checkin/checkout sekadar untuk disimpan (bukan untuk hitung harga)
        $check_in = Carbon::parse($request->check_in);
        $check_out = Carbon::parse($request->check_out);

        // 2. SIMPAN TRANSAKSI
        // PENTING: Kita TIDAK menghitung harga lagi disini.
        // Kita ambil 'total_price' yang dikirim dari Controller (Controller sudah benar 880.000)
        
        return Transaction::create([
            'user_id'     => auth()->id(), 
            'customer_id' => $customer->id,
            'room_id'     => $room->id,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'status'      => 'Reservation', 
            
            'breakfast'   => $request->breakfast ?? 'No',
            
            // [KUNCI PERBAIKAN] 
            // Ambil langsung dari Request yang dikirim Controller.
            // Jangan ada rumus matematika lagi disini.
            'total_price' => $request->total_price 
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
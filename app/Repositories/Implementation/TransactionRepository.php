<?php

namespace App\Repositories\Implementation;

use App\Models\Transaction;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function store($request, $customer, $room)
    {
        // 1. Hitung Durasi (Hari)
        $check_in = Carbon::parse($request->check_in);
        $check_out = Carbon::parse($request->check_out);
        $duration = $check_in->diffInDays($check_out) ?: 1; // Minimal 1 hari

        // 2. Hitung Komponen Harga
        // A. Harga Kamar
        $roomPrice = $room->price * $duration;

        // B. Harga Breakfast (Jika Yes, 140rb/malam)
        $breakfastPrice = 0;
        if ($request->breakfast == 'Yes') { 
            $breakfastPrice = 140000 * $duration; 
        }

        // C. Subtotal
        $subTotal = $roomPrice + $breakfastPrice;

        // D. Pajak PB1 10%
        $tax = $subTotal * 0.10;

        // E. Total Akhir
        $totalPrice = $subTotal + $tax;

        return Transaction::create([
            'user_id'     => auth()->id(), 
            'customer_id' => $customer->id,
            'room_id'     => $room->id,
            'check_in'    => $request->check_in,
            'check_out'   => $request->check_out,
            'status'      => 'Reservation', 
            'breakfast'   => $request->breakfast ?? 'No',
            'total_price' => $totalPrice // Harga fix (Kamar + Breakfast + Pajak)
        ]);
    }

    public function getTransaction($request)
    {
        // Logic DataTables (Tetap sama)
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
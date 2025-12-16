<?php

namespace App\Repositories\Implementation;

use App\Models\Transaction;
use App\Repositories\Interface\ReservasiKamarRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservasiKamarRepository implements ReservasiKamarRepositoryInterface
{
    public function getDatatable(Request $request)
    {
        $columns = [
            0 => 'transactions.id',
            1 => 'customers.name',
            2 => 'rooms.number',
            3 => 'transactions.check_in',
            4 => 'transactions.check_out',
            5 => 'transactions.id',
            6 => 'rooms.price',
            7 => 'transactions.status',
            8 => 'transactions.id',
        ];

        // QUERY UTAMA
        $query = Transaction::query()
            ->select([
                'transactions.*',
                'customers.name as customer_name',
                'rooms.number as room_number',
                'rooms.price as room_price',
                'types.name as type_name'
            ])
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('types', 'rooms.type_id', '=', 'types.id')
            
            // === [FILTER WAKTU (YANG SUDAH DIPERBAIKI)] ===
            // Ganti logika: Jangan filter berdasarkan check_in >= hari ini.
            // Tapi filter berdasarkan check_out >= hari ini.
            // Artinya: Selama belum waktunya pulang (check-out), data ini akan TETAP MUNCUL.
            // Contoh: Check In tgl 15, sekarang tgl 16, Check Out tgl 17. 
            // Karena 17 >= 16 (TRUE), data akan muncul.
            ->whereDate('transactions.check_out', '>=', Carbon::today())
            
            // === [FILTER STATUS] ===
            // Hanya tampilkan yang statusnya masih 'Reservation'
            ->where('transactions.status', 'Reservation'); 

        // SEARCHING
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('transactions.status', 'LIKE', "%{$search}%");
            });
        }

        // ORDERING
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIdx = $request->input('order.0.column', 0);
        $orderCol = $columns[$orderIdx] ?? 'transactions.check_in';
        $orderDir = $request->input('order.0.dir', 'asc');

        // COUNTING
        $countQuery = clone $query;
        $totalFiltered = $countQuery->count();

        // GET DATA
        $models = $query->orderBy($orderCol, $orderDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($models as $t) {
            $checkIn  = Carbon::parse($t->check_in);
            $checkOut = Carbon::parse($t->check_out);
            $duration = $checkIn->diffInDays($checkOut) ?: 1;
            
            // Logic Breakfast
            $rawBreakfast = $t->breakfast ?? 'No';
            $breakfast = (strtolower($rawBreakfast) === 'yes' || $rawBreakfast == '1') ? 'Yes' : 'No';

            // Hitung Harga
            $roomTotal = $duration * $t->room_price;
            $breakfastTotal = ($breakfast === 'Yes') ? ($duration * 140000) : 0;
            $subTotal = $roomTotal + $breakfastTotal;
            $finalTotal = $subTotal + ($subTotal * 0.10); 

            $data[] = [
                'id'            => $t->id,
                'customer_name' => $t->customer_name, 
                'room_info'     => [
                    'number' => $t->room_number,
                    'type'   => $t->type_name
                ],
                'check_in'      => $checkIn->format('d/m/Y'),
                'check_out'     => $checkOut->format('d/m/Y'),
                'breakfast'     => $breakfast, 
                'total_price'   => $finalTotal, 
                'status'        => $t->status,
                'raw_id'        => $t->id 
            ];
        }

        return [
            'draw'            => intval($request->input('draw')),
            
            // === [FIX JUGA TOTAL RECORD DISINI] ===
            // Pastikan hitungan total paginasi juga menggunakan logika yang sama (check_out)
            'recordsTotal'    => Transaction::where('status', 'Reservation')
                                            ->whereDate('check_out', '>=', Carbon::today())
                                            ->count(),
                                            
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ];
    }
}
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
        // Mapping Indeks Kolom dari JS ke Database untuk Sorting
        $columns = [
            0 => 'transactions.id',
            1 => 'customers.name',
            2 => 'transactions.count_person', // Kolom Tamu
            3 => 'rooms.number',
            4 => 'transactions.check_in',
            5 => 'transactions.check_out',
            6 => 'transactions.breakfast',
            7 => 'transactions.total_price',
            8 => 'transactions.status',
            9 => 'transactions.id',
        ];

        // 1. QUERY UTAMA
        $query = Transaction::query()
            ->select([
                'transactions.id',
                'transactions.check_in',
                'transactions.check_out',
                'transactions.total_price',
                'transactions.status',
                'transactions.breakfast',
                
                // [PENTING] Ambil kolom ini agar tidak undefined
                'transactions.count_person', 
                'transactions.count_child',

                'customers.name as customer_name',
                'rooms.number as room_number',
                'rooms.price as room_price', // Harga dasar kamar
                'types.name as type_name'
            ])
            ->join('customers', 'transactions.customer_id', '=', 'customers.id')
            ->join('rooms', 'transactions.room_id', '=', 'rooms.id')
            ->join('types', 'rooms.type_id', '=', 'types.id')
            
            // Filter: Hanya tampilkan reservasi yang belum lewat (checkout >= hari ini)
            ->whereDate('transactions.check_out', '>=', Carbon::today())
            
            // Filter: Status Reservation
            ->where('transactions.status', 'Reservation');

        // 2. SEARCHING
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('transactions.status', 'LIKE', "%{$search}%");
            });
        }

        // 3. ORDERING
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIdx = $request->input('order.0.column', 0);
        $orderCol = $columns[$orderIdx] ?? 'transactions.check_in';
        $orderDir = $request->input('order.0.dir', 'asc');

        // 4. COUNTING (Pagination Logic)
        $countQuery = clone $query;
        $totalFiltered = $countQuery->count();

        // 5. GET DATA
        // Handle "Show All" jika limit -1
        if ($limit != -1) {
            $query->offset($start)->limit($limit);
        }
        
        $models = $query->orderBy($orderCol, $orderDir)->get();

        // 6. MAPPING DATA KE JSON
        $data = [];
        foreach ($models as $t) {
            $checkIn  = Carbon::parse($t->check_in);
            $checkOut = Carbon::parse($t->check_out);
            
            // Logic Breakfast Display
            $rawBreakfast = $t->breakfast ?? 'No';
            $breakfast = (strtolower($rawBreakfast) === 'yes' || $rawBreakfast == '1') ? 'Yes' : 'No';

            $data[] = [
                'id'            => $t->id,
                'customer_name' => $t->customer_name, 
                
                // Data Tamu (Default 0 jika null agar JS tidak error)
                'count_person'  => $t->count_person ?? 1, 
                'count_child'   => $t->count_child ?? 0,

                'room_info'     => [
                    'number' => $t->room_number,
                    'type'   => $t->type_name
                ],
                'check_in'      => $checkIn->format('d/m/Y'),
                'check_out'     => $checkOut->format('d/m/Y'),
                'breakfast'     => $breakfast, 
                'total_price'   => $t->total_price, // Harga final dari database
                'status'        => $t->status,
                'raw_id'        => $t->id 
            ];
        }

        // Hitung Total Data (Tanpa Filter)
        $totalData = Transaction::where('status', 'Reservation')
                        ->whereDate('check_out', '>=', Carbon::today())
                        ->count();

        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ];
    }
}
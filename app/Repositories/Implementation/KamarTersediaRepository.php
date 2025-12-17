<?php

namespace App\Repositories\Implementation;

use App\Models\Room;
use App\Repositories\Interface\KamarTersediaRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KamarTersediaRepository implements KamarTersediaRepositoryInterface
{
    public function getDatatable(Request $request)
    {
        $columns = [
            0 => 'rooms.number',
            1 => 'rooms.number',
            2 => 'rooms.name',
            3 => 'types.name',
            4 => 'rooms.area_sqm',
            5 => 'rooms.room_facilities',
            6 => 'rooms.bathroom_facilities',
            7 => 'rooms.capacity',
            8 => 'rooms.price',
            9 => 'rooms.id', // Kita akan inject status text disini
        ];

        // 0. Default Tanggal Cek (Hari Ini)
        $checkDate = $request->input('check_date') 
            ? Carbon::parse($request->check_date) 
            : Carbon::today();

        // 1. QUERY UTAMA
        // Kita butuh load relasi 'transactions' untuk mengecek status checkout hari ini
        $query = Room::query()
            ->select([
                'rooms.*',
                'types.name as type_name'
            ])
            ->join('types', 'rooms.type_id', '=', 'types.id')
            ->distinct();

        // 2. LOGIKA KETERSEDIAAN (YANG LEBIH FLEKSIBEL)
        // Aturan: Tampilkan kamar yang KOSONG besoknya.
        // Jadi kalau ada tamu checkout HARI INI, kamar ini TETAP MUNCUL (Bisa dipesan untuk malam ini).
        $query->whereDoesntHave('transactions', function($q) use ($checkDate) {
            $q->where(function($sub) use ($checkDate) {
                // Logika: Cari yang jadwalnya "Menabrak" sampai BESOK.
                // Jika check_out == checkDate (Hari ini), dia tidak kena filter ini (Bisa muncul).
                $sub->whereDate('check_in', '<=', $checkDate)
                    ->whereDate('check_out', '>', $checkDate); 
            })
            ->whereNotIn('status', ['Cancel', 'Checked Out', 'Done']);
        });

        // ... (Filter Tipe & Search sama seperti sebelumnya) ...
        if ($request->has('type') && $request->type != 'All') {
            $query->where('rooms.type_id', $request->type);
        }

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('rooms.number', 'LIKE', "%{$search}%")
                  ->orWhere('rooms.name', 'LIKE', "%{$search}%")
                  ->orWhere('types.name', 'LIKE', "%{$search}%");
            });
        }

        // Pagination
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIdx = $request->input('order.0.column', 0);
        $orderCol = $columns[$orderIdx] ?? 'rooms.number';
        $orderDir = $request->input('order.0.dir', 'asc');

        $countQuery = clone $query;
        $totalFiltered = $countQuery->count();

        // Eager Load 'transactions' untuk pengecekan status di loop
        $models = $query->with(['transactions' => function($q) use ($checkDate) {
                // Ambil transaksi yang aktif pada tanggal cek
                $q->whereDate('check_in', '<=', $checkDate)
                  ->whereDate('check_out', '>=', $checkDate) // Cek yang checkout hari ini
                  ->whereNotIn('status', ['Cancel', 'Checked Out', 'Done']);
            }])
            ->orderBy($orderCol, $orderDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        // 6. FORMAT DATA (STATUS DINAMIS)
        $data = [];
        foreach ($models as $room) {
            
            // --- LOGIKA STATUS PINTAR ---
            $statusText = 'Tersedia'; // Default
            
            // Cek 1: Apakah Fisik Kamar sedang dibersihkan?
            if ($room->status === 'Cleaning') {
                $statusText = 'Sedang Dibersihkan';
            } 
            // Cek 2: Apakah ada transaksi aktif hari ini?
            elseif ($room->transactions->count() > 0) {
                // Ada transaksi aktif, pasti ini yang check-out nya hari ini (karena filter di atas)
                $statusText = 'Menunggu Checkout';
            }

            $data[] = [
                'id'                  => $room->id,
                'number'              => $room->number,
                'name'                => $room->name,
                'type'                => $room->type_name,
                'area_sqm'            => $room->area_sqm,
                'room_facilities'     => $room->room_facilities,
                'bathroom_facilities' => $room->bathroom_facilities,
                'capacity'            => $room->capacity,
                'price'               => $room->price,
                
                // Kirim status text ini ke Frontend agar JS bisa menampilkan badge warna-warni
                'status'              => $statusText 
            ];
        }

        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => Room::count(),
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ];
    }
}
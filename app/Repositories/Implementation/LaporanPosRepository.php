<?php

namespace App\Repositories\Implementation;

use App\Models\TransactionPos;
use App\Repositories\Interface\LaporanPosRepositoryInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanPosRepository implements LaporanPosRepositoryInterface
{
    /**
     * Query Dasar: Filter Tanggal & Search Manual
     */
    public function getLaporanPosQuery(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        
        // Ambil input search dari DataTables (bisa berupa array atau string)
        $searchDatatableValue = $request->input('search.value');
        $search = $searchDatatableValue;

        // Eager Load relasi menu agar performa cepat
        $query = TransactionPos::with(['details.menu']);

        // 1. Filter Tanggal
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00', 
                $endDate . ' 23:59:59'
            ]);
        }

        // 2. Filter Pencarian Manual (Search Box)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhere('payment_method', 'LIKE', "%{$search}%");
            });
        }

        // Default Order: Transaksi terbaru di atas
        $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * DataTables MANUAL (Tanpa Library Yajra)
     * Menggunakan Offset & Limit standard SQL
     */
    public function getLaporanPosDatatable(Request $request)
    {
        // 1. Ambil Query Dasar
        $query = $this->getLaporanPosQuery($request);

        // 2. Hitung Total Data (Untuk Pagination)
        $totalData = TransactionPos::count(); // Total semua data di DB
        $totalFiltered = $query->count();     // Total data setelah filter tanggal/search

        // 3. Ambil Parameter Pagination dari Request DataTables
        $limit = $request->input('length'); // Berapa baris per halaman
        $start = $request->input('start');  // Mulai dari baris ke berapa
        
        // Terapkan Limit & Offset (jika ada)
        if ($limit && $limit != -1) {
            $query->offset($start)->limit($limit);
        }

        // 4. Eksekusi Query
        $models = $query->get();

        // 5. Mapping Data ke Format JSON Array
        $data = [];
        foreach ($models as $row) {
            
            // Format Item Menu
            $itemList = '-';
            if ($row->details && $row->details->count() > 0) {
                $itemList = $row->details->map(function($detail) {
                    $namaMenu = $detail->menu ? $detail->menu->name : 'Item Terhapus';
                    return $namaMenu . ' (' . $detail->qty . ')';
                })->implode('<br>'); // Gunakan <br> agar rapi ke bawah
            }

            // Susun data sesuai urutan kolom di HTML (index.blade.php)
            // Sesuaikan urutan ini dengan <th> di view Anda
            $data[] = [
                'DT_RowIndex' => $start + count($data) + 1, // Nomor Urut
                'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '-',
                'invoice_number' => $row->invoice_number,
                'items' => $itemList,
                'total_amount' => $row->total_amount, 
                'pay_amount'   => $row->pay_amount,
                'change_amount'=> $row->change_amount,
                'payment_method' => ucfirst($row->payment_method),
                'action' => '' // Kosongkan jika belum ada tombol aksi
            ];
        }

        // 6. Return JSON Format DataTables (Legacy Style / Manual)
        // Format ini PASTI dikenali oleh DataTables JS
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,       // Wajib ada
            'recordsFiltered' => $totalFiltered,// Wajib ada
            'data' => $data                     // Wajib ada (Modern DataTables pakai 'data', versi lama 'aaData')
        ]);
    }
}
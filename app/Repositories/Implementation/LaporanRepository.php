<?php

namespace App\Repositories\Implementation;

use App\Models\RapatTransaction;
use App\Repositories\Interface\LaporanRepositoryInterface;
use Carbon\Carbon;

class LaporanRepository implements LaporanRepositoryInterface
{
    /**
     * Query Dasar: Digunakan bersama oleh DataTables DAN Export Excel.
     */
    public function getLaporanRapatQuery($request)
    {
        $startDate = $request->input('tanggal_mulai');
        $endDate = $request->input('tanggal_selesai');
        
        // --- PERBAIKAN PENTING UNTUK MENCEGAH ARRAY TO STRING CONVERSION ---
        // 1. Prioritaskan pengambilan nilai dari 'search.value' (untuk DataTables)
        $searchDatatableValue = $request->input('search.value');
        
        // 2. Jika bukan dari DataTables (misalnya dari URL Export), gunakan 'search'
        $searchUrlValue = $request->input('search');
        
        // Pilih nilai search yang paling relevan (string), jika ada
        $search = $searchDatatableValue ?: $searchUrlValue;
        
        if (is_array($search)) {
            // Jika masih array (misal, struktur DataTables yang lebih kompleks),
            // paksa ambil 'value' atau jadikan null
            $search = $search['value'] ?? null;
        }
        // --------------------------------------------------------------------

        $nowFormatted = Carbon::now()->format('Y-m-d H:i:s');

        $query = RapatTransaction::select('rapat_transactions.*')
            ->join('rapat_customers', 'rapat_transactions.rapat_customer_id', '=', 'rapat_customers.id')
            ->join('ruang_rapat_pakets', 'rapat_transactions.ruang_rapat_paket_id', '=', 'ruang_rapat_pakets.id')
            ->with(['rapatCustomer', 'ruangRapatPaket']); 

        // Filter 1: Hanya yang sudah selesai
        $query->whereRaw("CONCAT(rapat_transactions.tanggal_pemakaian, ' ', rapat_transactions.waktu_selesai) <= ?", [$nowFormatted]);

        // Filter 2: Rentang Tanggal
        if ($startDate) {
            $query->where('rapat_transactions.tanggal_pemakaian', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('rapat_transactions.tanggal_pemakaian', '<=', $endDate);
        }

        // Filter 3: Pencarian Teks
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('rapat_customers.instansi', 'LIKE', "%{$search}%")
                  ->orWhere('rapat_customers.nama', 'LIKE', "%{$search}%")
                  ->orWhere('ruang_rapat_pakets.name', 'LIKE', "%{$search}%");
            });
        }
        
        // Default urutan: Terbaru
        $query->orderBy('rapat_transactions.tanggal_pemakaian', 'DESC');

        return $query;
    }

    /**
     * Khusus DataTables: Mengambil query dasar + Pagination.
     */
    public function getLaporanRapatDatatable($request)
    {
        $query = $this->getLaporanRapatQuery($request); 

        $columns = [
            0 => 'rapat_customers.instansi',
            1 => 'rapat_transactions.tanggal_pemakaian',
            2 => 'rapat_transactions.waktu_mulai',
            3 => 'ruang_rapat_pakets.name',
            4 => 'rapat_transactions.jumlah_peserta',
            5 => 'rapat_transactions.total_pembayaran', 
            6 => 'rapat_transactions.status_pembayaran',
        ];

        $totalData = RapatTransaction::count();
        $totalFiltered = $query->count(); 

        // --- PAGINATION ---
        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir') ?? 'desc';

        $orderBy = $columns[$orderColumnIndex] ?? 'rapat_transactions.tanggal_pemakaian';

        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        // Terapkan sorting
        $query->orderBy($orderBy, $orderDir);

        $models = $query->get();

        // Mapping Data JSON
        $data = [];
        foreach ($models as $model) {
            // Pastikan Helper::dateFormat ada dan berfungsi
            $tanggal = \App\Helpers\Helper::dateFormat($model->tanggal_pemakaian);
            
            $data[] = [
                'instansi' => $model->rapatCustomer->instansi ?? '-',
                'tanggal' => $tanggal,
                'waktu' => $model->waktu_mulai . ' - ' . $model->waktu_selesai,
                'paket' => $model->ruangRapatPaket->name ?? '-',
                'jumlah_peserta' => $model->jumlah_peserta . ' Orang',
                'total_pembayaran' => $model->total_pembayaran ?? 0, 
                'status' => $model->status_pembayaran,
            ];
        }

        // Kunci DataTables versi lama: iTotalRecords, iTotalDisplayRecords, aaData
        return response()->json([
            'draw' => intval($request->input('draw')),
            'iTotalRecords' => $totalData,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData' => $data,
        ]);
    }
}
<?php

namespace App\Repositories\Interface;

use Illuminate\Http\Request;

interface LaporanPosRepositoryInterface
{
    /**
     * Get query untuk laporan POS dengan filter tanggal
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getLaporanPosQuery(Request $request);

    /**
     * Get data untuk DataTables (Ajax)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLaporanPosDatatable(Request $request);
}
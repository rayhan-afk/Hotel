<?php

namespace App\Repositories\Interface;

interface LaporanKamarRepositoryInterface
{
    /**
     * Get the query builder for the room reservation report.
     * Useful for exporting data (Excel/CSV) or manual pagination.
     *
     * @param mixed $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getLaporanKamarQuery($request);

    /**
     * Get the data formatted specifically for DataTables (JSON response).
     * Handles server-side pagination, searching, and sorting.
     *
     * @param mixed $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLaporanKamarDatatable($request);

    public function saveToLaporan($transaction);

}
<?php

namespace App\Repositories\Interface;

use Illuminate\Http\Request;

interface ReservasiKamarRepositoryInterface
{
    // Method untuk mengambil data format Datatables
    public function getDatatable(Request $request);
}
<?php

namespace App\Repositories\Interface;

use Illuminate\Http\Request;

interface KamarDibersihkanRepositoryInterface
{
    public function getDatatable(Request $request);
}
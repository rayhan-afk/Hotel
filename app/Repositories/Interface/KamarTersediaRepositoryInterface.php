<?php

namespace App\Repositories\Interface;

use Illuminate\Http\Request;

interface KamarTersediaRepositoryInterface
{
    public function getDatatable(Request $request);
}
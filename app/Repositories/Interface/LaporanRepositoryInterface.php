<?php

namespace App\Repositories\Interface;

interface LaporanRepositoryInterface
{
    public function getLaporanRapatDatatable($request);
    public function getLaporanRapatQuery($request);
}
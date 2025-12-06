<?php

namespace App\Repositories\Interface;

interface RuangRapatPaketRepositoryInterface
{
    public function getPakets($request);
    public function getPaketsDatatable($request);
}
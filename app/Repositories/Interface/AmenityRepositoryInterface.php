<?php

namespace App\Repositories\Interface;

interface AmenityRepositoryInterface
{
    public function getAmenities($request);

    public function getAmenitiesDatatable($request);
}
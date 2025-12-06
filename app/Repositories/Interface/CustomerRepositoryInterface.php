<?php

namespace App\Repositories\Interface;

interface CustomerRepositoryInterface
{
    public function get($request); // Atau getCustomers($request) jika sudah diubah

    public function count($request);

    // HAPUS 'static' DARI SINI
    public function store($request); 
}
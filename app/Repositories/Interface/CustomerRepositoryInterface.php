<?php

namespace App\Repositories\Interface;

interface CustomerRepositoryInterface
{
    // Method baru untuk Datatable Server-side
    public function getCustomersDatatable($request);

    // Method untuk mengambil data (Pagination biasa)
    public function get($request);

    // Method untuk menghitung total data
    public function count($request);

    // Method untuk menyimpan data customer baru
    public function store($request);
}
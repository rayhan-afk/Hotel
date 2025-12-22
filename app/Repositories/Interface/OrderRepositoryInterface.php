<?php

namespace App\Repositories\Interface;;

interface OrderRepositoryInterface
{
    // Method untuk menangani transaksi POS
    public function createTransaction(array $data);
}
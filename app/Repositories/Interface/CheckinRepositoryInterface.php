<?php

namespace App\Repositories\Interface;

interface CheckinRepositoryInterface
{
    // Ambil data untuk DataTables
    public function getCheckinDatatable($request);
    
    // Ambil satu data transaksi
    public function getTransaction($id);
    
    // Update data reservasi (misal ganti kamar/tanggal)
    public function update($request, $id);
    
    // Hapus reservasi
    public function delete($id);
}
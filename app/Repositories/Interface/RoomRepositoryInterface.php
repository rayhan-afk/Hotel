<?php

namespace App\Repositories\Interface;

use Illuminate\Http\Request;

interface RoomRepositoryInterface
{
    // Mengambil daftar kamar (Pagination & Search)
    public function getRooms(Request $request);

    // Mengambil data untuk Datatable (AJAX)
    public function getRoomsDatatable(Request $request);

    // Mengambil satu kamar berdasarkan ID
    public function getRoomById($id);

    // Menyimpan kamar baru
    public function store(Request $request);

    // Memperbarui data kamar
    public function update($room, Request $request);

    // Menghapus kamar
    public function delete($room);
}
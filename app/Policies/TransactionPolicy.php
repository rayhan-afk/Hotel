<?php

namespace App\Policies;

use App\Models\Transaction; // Import Model Transaction Anda
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Tentukan apakah User dapat melihat semua Transactions (akses umum dashboard/index).
     * Biasanya diizinkan untuk semua role manajemen (Super, Admin, Manager).
     */
    public function viewAny(User $user)
    {
        // Izinkan jika user adalah Super, Admin, atau Manager.
        return $user->isSuper() || $user->isAdmin() || $user->isManager()
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk melihat daftar transaksi.');
    }
    
    // ... (Anda dapat menambahkan metode view, create, update, delete, dll. di sini)

    /**
     * Tentukan apakah User dapat melakukan approval pada Transaction.
     * Hak ini hanya untuk Super dan Manager.
     * Inilah inti dari kebutuhan Anda.
     */
    public function approve(User $user, Transaction $transaction)
    {
        // 1. Jika User adalah Super, selalu izinkan.
        if ($user->isSuper()) {
            return Response::allow();
        }

        // 2. Jika User adalah Manager, izinkan.
        if ($user->isManager()) {
            return Response::allow();
        }

        // 3. Jika bukan Super atau Manager, tolak.
        return Response::deny('Hanya Super Admin dan Manager yang dapat menyetujui transaksi.');
    }
    
    /**
     * Tentukan apakah User dapat melakukan approval pada Ruang Rapat (misalnya jika logikanya terpisah)
     * Kita asumsikan transaksi ruang rapat juga menggunakan model Transaction atau memiliki logikanya sendiri.
     * Jika Anda memiliki model RuangRapatTransaction, Anda perlu membuat RapatTransactionPolicy.
     * Untuk saat ini, kita gabungkan di Policy ini jika Modelnya sama.
     */
    public function approveRapat(User $user)
    {
        // Hak approve Ruang Rapat juga diberikan kepada Super dan Manager
        return $user->isSuper() || $user->isManager()
            ? Response::allow()
            : Response::deny('Hanya Super Admin dan Manager yang dapat menyetujui Ruang Rapat.');
    }
}
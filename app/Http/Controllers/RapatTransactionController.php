<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RapatTransaction; // Model baru kita
use Carbon\Carbon; // Untuk filter tanggal

class RapatTransactionController extends Controller
{
    /**
     * Menampilkan daftar semua reservasi ruang rapat.
     */
    public function index(Request $request)
    {
        $query = RapatTransaction::with('rapatCustomer', 'ruangRapatPaket')
                    ->orderBy('tanggal_pemakaian', 'DESC');

        // Handle Search
        if ($request->filled('search')) {
            $query->where('id', $request->input('search'))
                  ->orWhereHas('rapatCustomer', function ($q) use ($request) {
                      $q->where('nama', 'like', '%' . $request->input('search') . '%');
                  });
        }

        // Filter untuk "Reservasi Aktif & Mendatang"
        $rapatTransactions = $query->clone()
            ->where('tanggal_pemakaian', '>=', Carbon::today())
            ->paginate(10, ['*'], 'active_page'); // Paginator terpisah

        // Filter untuk "Reservasi Selesai"
        $rapatTransactionsExpired = $query->clone()
            ->where('tanggal_pemakaian', '<', Carbon::today())
            ->paginate(10, ['*'], 'expired_page'); // Paginator terpisah

        return view('rapat.index', compact('rapatTransactions', 'rapatTransactionsExpired'));
    }

    /**
     * (Opsional) Menampilkan riwayat pembayaran.
     */
    public function paymentHistory(Request $request)
    {
        // TODO: Buat logika untuk menampilkan riwayat pembayaran rapat
        // $payments = RapatPayment::...
        // return view('rapat.payment.index', compact('payments'));
        return redirect()->route('rapat.transaction.index')->with('info', 'Halaman Riwayat Pembayaran Rapat belum dibuat.');
    }
}
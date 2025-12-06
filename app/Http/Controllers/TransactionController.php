<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return view('transaction.index', [
                'transactions' => $this->transactionRepository->getTransaction($request),
                'transactionsExpired' => $this->transactionRepository->getTransactionExpired($request)
            ]);
        }

        return view('transaction.index', [
            'transactions' => $this->transactionRepository->getTransaction($request),
            'transactionsExpired' => $this->transactionRepository->getTransactionExpired($request)
        ]);
    }
    public function approve(Transaction $transaction)
    {
        // 💡 PERUBAHAN UTAMA: Pengecekan Otorisasi
        // Cek apakah user saat ini diizinkan untuk melakukan aksi 'approve'
        // Terdefinisi di TransactionPolicy@approve
        $this->authorize('approve', $transaction); 

        // ----------------------------------------------------
        // Lanjutkan dengan LOGIKA APPROVAL transaksi kamar Anda:
        // ----------------------------------------------------
        
        // Contoh: Mengubah status transaksi
        // $transaction->status = 'Approved';
        // $transaction->approved_by = auth()->id();
        // $transaction->save();

        return redirect()->back()->with('success', 'Transaksi Kamar berhasil disetujui.');
    }
    
}

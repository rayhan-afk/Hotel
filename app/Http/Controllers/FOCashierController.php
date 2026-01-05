<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Helpers\Helper; 

class FOCashierController extends Controller
{
    // 1. HALAMAN UTAMA & SEARCH
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        // Ubah 'status' menjadi 'transactions.status' agar tidak ambigu
        $query = Transaction::with(['customer', 'room'])
            ->where('transactions.status', 'Check In'); 

        // Jika ada pencarian
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->whereHas('room', function($r) use ($keyword) {
                    $r->where('number', 'LIKE', "%{$keyword}%");
                })
                ->orWhereHas('customer', function($c) use ($keyword) {
                    $c->where('name', 'LIKE', "%{$keyword}%");
                });
            });
        }

        // Join ke rooms untuk sorting, Select transactions.* agar data model tidak tertimpa data kamar
        $transactions = $query->join('rooms', 'transactions.room_id', '=', 'rooms.id')
                              ->select('transactions.*') 
                              ->orderBy('rooms.number', 'asc')
                              ->paginate(10);

        return view('fo_cashier.index', compact('transactions', 'keyword'));
    }

    // 2. HALAMAN DETAIL (FOLIO) - TEMPAT INPUT SALES
    public function show($id)
    {
        // Ambil transaksi beserta history charges-nya
        $transaction = Transaction::with(['customer', 'room', 'charges'])->findOrFail($id);
        
        return view('fo_cashier.show', compact('transaction'));
    }

    // 3. SIMPAN SALES & UPDATE TOTAL TRANSAKSI (DIPERBAIKI: MERGE ITEM)
    public function storeCharge(Request $request, $id)
    {
        $request->validate([
            'type'      => 'required',
            'item_name' => 'required|string',
            'amount'    => 'required|numeric|min:0',
            'qty'       => 'required|integer|min:1',
        ]);

        $transaction = Transaction::findOrFail($id);

        // Hitung total dari inputan yang BARU ini
        $totalInputBaru = $request->amount * $request->qty;

        // === LOGIKA BARU: CEK APAKAH ITEM SUDAH ADA? ===
        $existingCharge = TransactionCharge::where('transaction_id', $id)
            ->where('item_name', $request->item_name) // Nama harus sama
            ->where('type', $request->type)           // Tipe harus sama
            ->where('amount', $request->amount)       // Harga harus sama persis (karena kalau harga beda, dianggap item beda)
            ->first();

        if ($existingCharge) {
            // SKENARIO A: Item Sudah Ada -> UPDATE QTY
            $newQty = $existingCharge->qty + $request->qty;
            $newTotal = $existingCharge->amount * $newQty;

            $existingCharge->update([
                'qty'   => $newQty,
                'total' => $newTotal,
                // Opsional: Gabungkan catatan jika ada note baru
                'note'  => $existingCharge->note . ($request->note ? ' | ' . $request->note : '')
            ]);
        } else {
            // SKENARIO B: Item Belum Ada -> BUAT BARU
            TransactionCharge::create([
                'transaction_id' => $id,
                'type'           => $request->type,
                'item_name'      => $request->item_name,
                'amount'         => $request->amount,
                'qty'            => $request->qty,
                'total'          => $totalInputBaru,
                'note'           => $request->note
            ]);
        }

        // 2. UPDATE TOTAL_PRICE DI TABEL TRANSACTION (PENTING!)
        // Apapun skenarionya (Merge atau Baru), Grand Total tetap bertambah sejumlah inputan baru
        $transaction->increment('total_price', $totalInputBaru);
        
        return redirect()->back()->with('success', 'Transaksi berhasil ditambahkan & Total diperbarui!');
    }
    
    // 4. HAPUS SALES & UPDATE TOTAL TRANSAKSI
    public function destroyCharge($id)
    {
        $charge = TransactionCharge::findOrFail($id);
        $transaction = Transaction::findOrFail($charge->transaction_id);

        // 1. Kurangi Total Price Transaksi
        $transaction->total_price -= $charge->total;
        $transaction->save();

        // 2. Hapus Data Charge
        $charge->delete();

        return redirect()->back()->with('success', 'Item berhasil dihapus & Total diperbarui.');
    }
}
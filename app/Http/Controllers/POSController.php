<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Wajib ada untuk DB::beginTransaction()
use App\Models\Menu;
use App\Models\TransactionPos; // Model Header Baru
use App\Models\TransactionPosDetail; // Model Detail Baru

class PosController extends Controller
{
    // --- 1. MENAMPILKAN HALAMAN KASIR ---
    public function index()
    {
        $menus = Menu::where('is_available', 1)->get();
        // Mengambil kategori unik untuk filter
        $categories = Menu::select('category')->distinct()->get();

        return view('pos.index', compact('menus', 'categories'));
    }

    // --- 2. PROSES SIMPAN TRANSAKSI (Updated - AUTO POTONG BAHAN BAKU) ---
    public function store(Request $request)
    {
        // Validasi data yang dikirim dari JS
        
        try {
            DB::beginTransaction();

            // A. Generate Invoice Unik (POS-Tanggal-Random)
            $invoice = 'POS-' . date('dmY') . '-' . rand(1000, 9999);

            // B. Simpan ke Tabel Header (TransactionPos)
            $transaction = TransactionPos::create([
                'invoice_number' => $invoice,
                'total_amount'   => $request->total_amount,
                'pay_amount'     => $request->pay_amount,
                'change_amount'  => $request->pay_amount - $request->total_amount, 
                'payment_method' => $request->payment_method ?? 'Tunai',
            ]);

            // C. Simpan Detail Item (Looping Cart)
            foreach ($request->cart as $item) {
                
                // 1. Simpan ke Tabel Detail Transaksi
                TransactionPosDetail::create([
                    'transaction_id' => $transaction->id,
                    'menu_id'        => $item['id'], // ID Menu
                    'qty'            => $item['qty'],
                    'price'          => $item['price']
                ]);

                // 2. LOGIKA BARU: POTONG STOK BAHAN BAKU (INGREDIENTS)
                // Mengambil resep dari tabel 'menu_ingredients' berdasarkan menu_id
                $resep = DB::table('menu_ingredients')
                            ->where('menu_id', $item['id'])
                            ->get();

                // Jika menu ini punya resep (bukan menu kosong)
                if ($resep->count() > 0) {
                    foreach ($resep as $bahan) {
                        
                        // --- HITUNG TOTAL PENGURANGAN ---
                        // Rumus: (Butuh berapa per porsi) * (Jumlah porsi yang dibeli)
                        
                        // PENTING: Pastikan nama kolom 'quantity' di tabel menu_ingredients benar.
                        // Jika di database namanya 'amount' atau 'qty', ganti kata 'quantity' di bawah ini.
                       $totalPengurangan = $bahan->quantity_needed * $item['qty'];

                        // --- UPDATE STOK DI GUDANG ---
                        // Update tabel 'ingredients'
                        
                        // PENTING: Pastikan nama kolom 'ingredient_id' benar.
                        // PENTING: Pastikan nama kolom 'stock' di tabel ingredients benar.
                        DB::table('ingredients')
                            ->where('id', $bahan->ingredient_id) 
                            ->decrement('stock', $totalPengurangan);
                    }
                }
            }

            DB::commit(); // Simpan permanen

            // Return response format JSON agar bisa dibaca JavaScript
            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi Berhasil!',
                'invoice' => $invoice
            ], 200);

        } catch (\Exception $e) {
            DB::rollback(); // Batalkan jika error
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- 3. HALAMAN RIWAYAT (Baru) ---
    public function history()
    {
        // Ambil data transaksi dari tabel baru, urutkan dari yang terbaru
        $transactions = TransactionPos::orderBy('created_at', 'desc')->get();
        
        return view('pos.history', compact('transactions'));
    }
    // --- 4. CETAK STRUK ---
    public function printStruk($invoice)
    {
        // Ambil data transaksi beserta detail item & nama menunya
        $transaction = TransactionPos::where('invoice_number', $invoice)
            ->with(['details.menu']) // Load relasi detail & menu
            ->firstOrFail();

        // Data Toko (Bisa hardcode atau ambil dari setting database)
        $store = [
            'name' => 'HOTEL SAWUNGGALING',
            'address' => 'Jl. Sawunggaling No.13, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40116',
            'phone' => '0819-1704-4390'
        ];

        return view('pos.print_struk', compact('transaction', 'store'));
    }
}
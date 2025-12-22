<?php

namespace App\Repositories\Implementation;

use App\Repositories\Interface\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderRepository implements OrderRepositoryInterface
{
    public function createTransaction(array $data)
    {
        // 1. Mulai Transaksi Database (Safety First!)
        DB::beginTransaction();

        try {
            // A. Buat Header Order
            $order = Order::create([
                'invoice_number' => 'INV-' . time(), // Generate nomor unik simple
                'user_id' => Auth::id() ?? 1, // User yang login (Kasir)
                'table_number' => $data['table_number'],
                'payment_status' => $data['payment_status'] ?? 'Unpaid',
                'status' => 'Pending',
                'total_price' => 0 // Nanti diupdate setelah hitung total
            ]);

            $grandTotal = 0;

            // B. Loop setiap menu yang dipesan
            foreach ($data['items'] as $item) {
                // Ambil data menu beserta resepnya
                $menu = Menu::with('ingredients')->findOrFail($item['menu_id']);
                
                $qtyOrder = $item['qty'];
                $subtotal = $menu->price * $qtyOrder;
                
                // C. Cek & Potong Stok Bahan Baku (LOGIKA UTAMA)
                foreach ($menu->ingredients as $ingredient) {
                    // Hitung total bahan yang dibutuhkan untuk order ini
                    // (Butuh per porsi * Jumlah Order)
                    $totalIngredientNeeded = $ingredient->pivot->quantity_needed * $qtyOrder;

                    // Validasi: Apakah stok cukup?
                    if ($ingredient->stock < $totalIngredientNeeded) {
                        throw new Exception("Stok {$ingredient->name} tidak cukup! (Sisa: {$ingredient->stock}, Butuh: {$totalIngredientNeeded})");
                    }

                    // Eksekusi Potong Stok
                    $ingredient->decrement('stock', $totalIngredientNeeded);
                }

                // D. Simpan Detail Item ke Database
                $order->items()->create([
                    'menu_id' => $menu->id,
                    'qty' => $qtyOrder,
                    'price' => $menu->price,
                    'subtotal' => $subtotal
                ]);

                $grandTotal += $subtotal;
            }

            // E. Update Total Harga di Header Order
            $order->update(['total_price' => $grandTotal]);

            // Jika sukses semua, simpan permanen
            DB::commit();
            return $order;

        } catch (Exception $e) {
            // Jika ada satu saja error, batalkan semua perubahan
            DB::rollback();
            throw $e; // Lempar error ke Controller
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Room;        // <--- [BARU] Perlu Model Room
use App\Models\Customer;    // <--- [BARU] Perlu Model Customer
use App\Models\TypePrice;   // <--- [BARU] Perlu Model Harga Spesial
use Carbon\Carbon;          // <--- [BARU] Untuk hitung tanggal
use Carbon\CarbonPeriod;    // <--- [BARU] Untuk loop tanggal
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
        // Cek apakah user saat ini diizinkan untuk melakukan aksi 'approve'
        $this->authorize('approve', $transaction); 

        // Lanjutkan dengan LOGIKA APPROVAL transaksi (sesuai kebutuhan Anda)
        // $transaction->update(['status' => 'Paid']); // Contoh sederhana

        return redirect()->back()->with('success', 'Transaksi Kamar berhasil disetujui.');
    }

    // =========================================================================
    // [BARU] LOGIC HITUNG HARGA OTOMATIS (SULTAN MODE) 👑
    // =========================================================================
    public function getCountPayment(Request $request)
    {
        // 1. Ambil Input dari AJAX
        $roomId = $request->room_id;
        $customerId = $request->customer_id;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        // Validasi Sederhana: Jika data belum lengkap, kembalikan 0
        if (!$roomId || !$checkIn || !$checkOut) {
            return response()->json(['total' => 0, 'text' => 'Rp 0']);
        }

        // 2. Ambil Data Kamar (Beserta Tipe-nya)
        $room = Room::with('type')->find($roomId);
        if (!$room) return response()->json(['total' => 0, 'text' => 'Rp 0']);

        // 3. Tentukan Grup Customer
        $customerGroup = 'WalkIn'; // Default jika tamu belum dipilih
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer && $customer->customer_group) {
                $customerGroup = $customer->customer_group;
            }
        }

        // 4. Siapkan Loop Tanggal
        try {
            $start = Carbon::parse($checkIn);
            $end = Carbon::parse($checkOut);
            
            // Validasi tanggal mundur
            if ($end->lessThanOrEqualTo($start)) {
                return response()->json(['total' => 0, 'text' => 'Rp 0 (Tanggal Invalid)']);
            }

            // Loop dari Check-in sampai H-1 Check-out (Malam menginap)
            $period = CarbonPeriod::create($start, $end->copy()->subDay());
            
            $totalPrice = 0;

            foreach ($period as $date) {
                $isWeekend = $date->isWeekend(); // Sabtu & Minggu = True
                
                // Cek apakah ada aturan harga khusus di database?
                $specialPrice = TypePrice::where('type_id', $room->type_id)
                                         ->where('customer_group', $customerGroup)
                                         ->first();

                $dailyPrice = 0;

                if ($specialPrice) {
                    // === Logic Harga Spesial ===
                    if ($isWeekend) {
                        // Jika harga weekend diisi (>0), pakai itu. Jika tidak, pakai harga default kamar.
                        $dailyPrice = $specialPrice->price_weekend > 0 
                                      ? $specialPrice->price_weekend 
                                      : $room->price;
                    } else {
                        // Weekday
                        $dailyPrice = $specialPrice->price_weekday > 0 
                                      ? $specialPrice->price_weekday 
                                      : $room->price;
                    }
                } else {
                    // === Logic Harga Default (Tidak ada aturan grup) ===
                    $dailyPrice = $room->price;
                }

                $totalPrice += $dailyPrice;
            }

            // 5. Kembalikan Hasil ke Javascript
            return response()->json([
                'total' => $totalPrice, // Angka (untuk database)
                'text'  => 'Rp ' . number_format($totalPrice, 0, ',', '.') // Teks (untuk label)
            ]);

        } catch (\Exception $e) {
            return response()->json(['total' => 0, 'text' => 'Error Calculation']);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Events\NewReservationEvent;
use App\Events\RefreshDashboardEvent;
use App\Helpers\Helper;
use App\Http\Requests\ChooseRoomRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Transaction;
use App\Models\TypePrice; 
use App\Models\User;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Repositories\Interface\RoomRepositoryInterface;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonPeriod; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionRoomReservationController extends Controller
{
    private $customerRepository;
    private $roomRepository;
    private $transactionRepository;

    // Definisikan Harga Sarapan
    private const BREAKFAST_PRICE = 100000; 

    public function __construct(
        TransactionRepositoryInterface $transactionRepository, 
        CustomerRepositoryInterface $customerRepository, 
        RoomRepositoryInterface $roomRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->customerRepository = $customerRepository;
        $this->roomRepository = $roomRepository;
    }

    public function pickFromCustomer(Request $request, CustomerRepositoryInterface $customerRepository)
    {
        $customers = $customerRepository->getCustomers($request);
        $customersCount = $customers->total(); 

        return view('transaction.reservation.pickFromCustomer', [
            'customers'      => $customers,
            'customersCount' => $customersCount,
        ]);
    }

    public function createIdentity()
    {
        return view('transaction.reservation.createIdentity');
    }

    public function storeCustomer(StoreCustomerRequest $request, CustomerRepositoryInterface $customerRepository)
    {
        $customer = $customerRepository->store($request);
        return redirect()
            ->route('transaction.reservation.viewCountPerson', ['customer' => $customer->id])
            ->with('success', 'Customer ' . $customer->name . ' created!');
    }

    public function viewCountPerson(Customer $customer)
    {
        return view('transaction.reservation.viewCountPerson', [
            'customer' => $customer,
        ]);
    }

    public function chooseRoom(ChooseRoomRequest $request, Customer $customer)
    {
        $stayFrom = $request->check_in;
        $stayUntil = $request->check_out;
        
        // 1. Hitung durasi hari
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) $dayDifference = 1;

        $occupiedRoomIds = $this->getOccupiedRoomID($stayFrom, $stayUntil);
        
        $query = Room::with('type')->whereNotIn('id', $occupiedRoomIds);

        if ($request->has('type_id') && $request->type_id != '') {
            $query->where('type_id', $request->type_id);
        }

        $sortPrice = $request->input('sort_price', 'ASC');
        $sortPrice = in_array(strtoupper($sortPrice), ['ASC', 'DESC']) ? strtoupper($sortPrice) : 'ASC';
        $query->orderBy('price', $sortPrice);

        $rooms = $query->paginate(10);
        $roomsCount = $rooms->total();

        // === [FIX 1] INJECT HARGA SULTAN KE LIST KAMAR ===
        $rooms->getCollection()->transform(function ($room) use ($customer, $stayFrom, $stayUntil, $dayDifference) {
            
            $totalPrice = $this->calculateRoomCost($room, $customer, $stayFrom, $stayUntil);
            
            $room->total_price_estimate = $totalPrice;
            $room->price_per_night_estimate = $totalPrice / $dayDifference;

            return $room;
        });

        return view('transaction.reservation.chooseRoom', [
            'customer' => $customer,
            'rooms' => $rooms,
            'stayFrom' => $stayFrom,
            'stayUntil' => $stayUntil,
            'roomsCount' => $roomsCount,
            'dayDifference' => $dayDifference 
        ]);
    }

    // === [METHOD YANG DIPERBAIKI] ===
    public function confirmation(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) $dayDifference = 1;

        // 1. Hitung Harga Kamar (Sultan Mode)
        $roomPriceTotal = $this->calculateRoomCost($room, $customer, $stayFrom, $stayUntil);

        // 2. [FIX] Hitung Sarapan (Agar Total Bayar Akurat)
        // Cek apakah ada input 'breakfast' dari step sebelumnya (via URL/Form)
        $breakfastPrice = 0;
        if ($request->input('breakfast') === 'Yes') {
            $breakfastPrice = self::BREAKFAST_PRICE * $dayDifference;
        }

        // 3. Hitung Total & Pajak
        $subTotal = $roomPriceTotal + $breakfastPrice;
        $tax = $subTotal * 0.10;
        
        // Total yang harus dibayar (LUNAS)
        $totalPayment = $subTotal + $tax; 

        $countPerson = $request->input('count_person', 1);

        return view('transaction.reservation.confirmation', [
            'customer' => $customer,
            'room' => $room,
            'stayFrom' => $stayFrom,
            'stayUntil' => $stayUntil,
            
            // Kirim harga TOTAL LUNAS ke variable 'downPayment' 
            // (Nama variabel tetap downPayment agar View tidak error, tapi isinya Full Payment)
            'downPayment' => $totalPayment, 
            
            'dayDifference' => $dayDifference,
            'minimumTax' => $tax,
            'countPerson' => $countPerson,
            'roomPriceTotal' => $roomPriceTotal,
            
            // [FIX] Kirim Total Harga Sarapan (bukan cuma harga satuan)
            'breakfastPrice' => $breakfastPrice > 0 ? $breakfastPrice : self::BREAKFAST_PRICE 
        ]);
    }

    // ===============================================================
    // METHOD 1: PREVIEW INVOICE (UNTUK RESERVASI AWAL)
    // ===============================================================
    public function previewInvoice(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $days = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($days < 1) $days = 1;

        $breakfast = $request->query('breakfast', 'No');

        // --- LOGIK HITUNG WEEKDAY/WEEKEND ---
        $calc = $this->calculateDetailPrice($room, $customer, $stayFrom, $stayUntil);
        
        $roomPriceTotal = $calc['total_price']; // Total Harga Kamar
        
        // Hitung Sarapan
        $breakfastPrice = ($breakfast === 'Yes') ? (self::BREAKFAST_PRICE * $days) : 0;
        
        $subTotal   = $roomPriceTotal + $breakfastPrice;
        $tax        = $subTotal * 0.10; 
        $grandTotal = $subTotal + $tax;

        $transactionCode = 'INV-PREVIEW'; // Dummy code

        $invoiceData = [
            'customer' => $customer,
            'room' => $room,
            'check_in' => $stayFrom,
            'check_out' => $stayUntil,
            'days' => $days,
            'breakfast_status' => $breakfast,
            
            // Data Rincian Weekday/Weekend
            'weekday_count' => $calc['weekday_count'],
            'weekend_count' => $calc['weekend_count'],
            'weekday_total' => $calc['weekday_total'],
            'weekend_total' => $calc['weekend_total'],
            'weekday_price_satuan' => $calc['weekday_price_satuan'],
            'weekend_price_satuan' => $calc['weekend_price_satuan'],

            'room_price_total' => $roomPriceTotal,
            'breakfast_price_total' => $breakfastPrice,
            'sub_total' => $subTotal, 
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'transaction_code' => $transactionCode,
            'date' => Carbon::now()->format('Y-m-d'),
            'user_name' => auth()->user()->name ?? 'Admin',
            'transaction' => null 
        ];

        return view('transaction.reservation.invoice_preview', $invoiceData);
    }
    
    // === [METHOD UTAMA SIMPAN RESERVASI (AUTO LUNAS)] ===
    public function payDownPayment(Customer $customer, Room $room, Request $request) 
    {
        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'breakfast' => 'required|in:Yes,No',
        ]);

        $occupiedRoomIds = $this->getOccupiedRoomID($request->check_in, $request->check_out);
        if ($occupiedRoomIds->contains($room->id)) {
            return redirect()->back()
                ->with('failed', 'Maaf, Kamar ini baru saja dipesan orang lain di tanggal yang sama.');
        }

        $dayDifference = Helper::getDateDifference($request->check_in, $request->check_out);
        if ($dayDifference < 1) $dayDifference = 1; 

        // 1. Hitung Harga Kamar (Sultan Mode)
        $roomPriceTotal = $this->calculateRoomCost($room, $customer, $request->check_in, $request->check_out);

        // 2. Hitung Harga Sarapan
        $breakfastPrice = ($request->breakfast === 'Yes') ? (self::BREAKFAST_PRICE * $dayDifference) : 0;
        
        // 3. Hitung Total & Pajak
        $subTotal       = $roomPriceTotal + $breakfastPrice;
        $tax            = $subTotal * 0.10; 
        $grandTotal     = $subTotal + $tax;

        // 4. Masukkan Harga Final ke Request
        // [PENTING] 'paid_amount' harus sama dengan 'total_price' agar statusnya LUNAS (Sisa Bayar 0)
        $request->merge([
            'total_price' => $grandTotal,
            'paid_amount' => $grandTotal, 
            'status'      => 'Reservation' 
        ]);

        // 5. Simpan ke Repository
        $this->transactionRepository->store($request, $customer, $room);
        
        try {
            event(new RefreshDashboardEvent('New reservation created'));
        } catch (\Exception $e) {
            // Reverb error ignore
        }

        return redirect()->route('dashboard.index')
            ->with('success', 'Reservasi Berhasil! Status Pembayaran Lunas & Tamu siap Check-In.');
    }

    private function getOccupiedRoomID($checkIn, $checkOut)
    {
        return Transaction::where(function($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            ->whereIn('status', ['Reservation', 'Check In']) 
            ->pluck('room_id');
    }

    // Fungsi Hitung Harga Dinamis
    private function calculateRoomCost($room, $customer, $checkIn, $checkOut)
    {
        try {
            $start = Carbon::parse($checkIn)->startOfDay();
            $end   = Carbon::parse($checkOut)->startOfDay();
            
            $period = CarbonPeriod::create($start, $end->copy()->subDay());
            
            $customerGroup = $customer->customer_group ?? 'WalkIn';
            
            $specialPrice = TypePrice::where('type_id', $room->type_id)
                                     ->where('customer_group', $customerGroup)
                                     ->first();

            $totalPrice = 0;
            $daysCount = 0; 

            foreach ($period as $date) {
                $daysCount++;
                $isWeekend = $date->isWeekend();
                $dailyPrice = 0;

                if ($specialPrice) {
                    if ($isWeekend) {
                        $dailyPrice = $specialPrice->price_weekend > 0 
                                    ? $specialPrice->price_weekend 
                                    : $room->price;
                    } else {
                        $dailyPrice = $specialPrice->price_weekday > 0 
                                    ? $specialPrice->price_weekday 
                                    : $room->price;
                    }
                } else {
                    $dailyPrice = $room->price;
                }

                $totalPrice += $dailyPrice;
            }

            if ($totalPrice == 0 || $daysCount == 0) {
                $diff = $start->diffInDays($end);
                if ($diff < 1) $diff = 1;
                return $room->price * $diff;
            }

            return $totalPrice;

        } catch (\Exception $e) {
            $diff = Helper::getDateDifference($checkIn, $checkOut);
            if ($diff < 1) $diff = 1;
            return $room->price * $diff;
        }
    }

    // ===============================================================
    // METHOD 2: PRINT INVOICE (FIXED: FLAT RATE & MIXED TAX)
    // ===============================================================
    public function printInvoice(Transaction $transaction)
    {
        $transaction->load(['customer', 'room', 'user']);

        // 1. KUPAS DULU ITEM EXTRA & PAJAK
        $extraBedTotal = ($transaction->extra_bed ?? 0) * 200000; 
        $extraBreakfastTotal = ($transaction->extra_breakfast ?? 0) * 125000;
        $nonTaxableTotal = $extraBedTotal + $extraBreakfastTotal;

        $grandTotal = $transaction->total_price;
        $taxableWithTax = $grandTotal - $nonTaxableTotal;
        $taxablePure = $taxableWithTax / 1.10; 
        $tax = $taxableWithTax - $taxablePure;

        // ---------------------------------------------------------
        // 2. AUTO-CORRECTION DURASI (SISTEM CERDAS)
        // ---------------------------------------------------------
        // Meskipun di DB checkoutnya tgl 26 (Early), sistem akan
        // menghitung ulang: "Uang segini itu jatah untuk berapa malam sih?"
        
        $paidForRoomAndBreakfast = $taxablePure; 
        $checkInDate = Carbon::parse($transaction->check_in);
        
        $foundDays = 0;
        $tolerance = 1000; // Toleransi pembulatan rupiah
        
        // Loop cek harga dari 1 sampai 30 malam
        for ($i = 1; $i <= 30; $i++) {
            $simulatedCheckOut = $checkInDate->copy()->addDays($i);
            
            $calc = $this->calculateDetailPrice($transaction->room, $transaction->customer, $checkInDate, $simulatedCheckOut);
            $simulatedRoomPrice = $calc['total_price'];
            
            $simulatedBreakfast = ($transaction->breakfast == 'Yes') ? (100000 * $i) : 0;
            $simulatedTotal = $simulatedRoomPrice + $simulatedBreakfast;

            // Jika harga simulasi == harga yang sudah dibayar
            if (abs($simulatedTotal - $paidForRoomAndBreakfast) < $tolerance) {
                $foundDays = $i; // KETEMU! Ini durasi aslinya.
                break; 
            }
        }

        // Tentukan Durasi & Tanggal Checkout untuk TAMPILAN
        if ($foundDays > 0) {
            $days = $foundDays;
            $displayCheckOut = $checkInDate->copy()->addDays($foundDays); // Tampilkan tgl checkout seharusnya (misal tgl 27)
        } else {
            // Fallback (jika harga custom)
            $days = Helper::getDateDifference($transaction->check_in, $transaction->check_out);
            if ($days < 1) $days = 1;
            $displayCheckOut = $transaction->check_out;
        }

        // 3. HITUNG RINCIAN AKHIR (PAKAI DURASI KOREKSI)
        $calc = $this->calculateDetailPrice($transaction->room, $transaction->customer, $transaction->check_in, $displayCheckOut);

        $breakfastPrice = ($transaction->breakfast == 'Yes') ? (100000 * $days) : 0;
        $roomPriceTotal = $calc['total_price'];

        $invoiceData = [
            'customer' => $transaction->customer,
            'room' => $transaction->room,
            
            // Tampilkan Jam Masuk Asli
            'check_in' => $transaction->check_in,
            
            // Tampilkan Tanggal Keluar Rencana (Biar harga match)
            // Tapi kalau mau jujur jam keluarnya, bisa pakai $transaction->check_out 
            // Cuma nanti durasi harinya yg kita paksa tampil "2 Malam".
            'check_out' => $displayCheckOut, 
            
            'days' => $days,
            'breakfast_status' => $transaction->breakfast,
            'weekday_count' => $calc['weekday_count'],
            'weekend_count' => $calc['weekend_count'],
            'weekday_total' => $calc['weekday_total'],
            'weekend_total' => $calc['weekend_total'],
            'weekday_price_satuan' => $calc['weekday_price_satuan'],
            'weekend_price_satuan' => $calc['weekend_price_satuan'],
            'room_price_total' => $roomPriceTotal,
            'breakfast_price_total' => $breakfastPrice,
            'sub_total' => $grandTotal - $tax,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'transaction_code' => 'INV-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($transaction->created_at)->format('Y-m-d'),
            'user_name' => $transaction->user->name ?? 'Admin',
            'transaction' => $transaction,
        ];

        return view('transaction.reservation.invoice_preview', $invoiceData);
    }
    // HELPER: FUNGSI HITUNG RINCIAN
    private function calculateDetailPrice($room, $customer, $checkIn, $checkOut)
    {
        $start = Carbon::parse($checkIn)->startOfDay();
        $end   = Carbon::parse($checkOut)->startOfDay();
        $period = CarbonPeriod::create($start, $end->copy()->subDay());
        
        $customerGroup = $customer->customer_group ?? 'WalkIn';
        $specialPrice = TypePrice::where('type_id', $room->type_id)
            ->where('customer_group', $customerGroup)
            ->first();

        $data = [
            'weekday_count' => 0, 'weekday_total' => 0, 'weekday_price_satuan' => $room->price,
            'weekend_count' => 0, 'weekend_total' => 0, 'weekend_price_satuan' => $room->price,
            'total_price' => 0
        ];

        foreach ($period as $date) {
            $isWeekend = $date->isWeekend(); 
            $dailyPrice = $room->price; 

            if ($specialPrice) {
                if ($isWeekend) {
                    $dailyPrice = $specialPrice->price_weekend > 0 ? $specialPrice->price_weekend : $room->price;
                } else {
                    $dailyPrice = $specialPrice->price_weekday > 0 ? $specialPrice->price_weekday : $room->price;
                }
            }

            if ($isWeekend) {
                $data['weekend_count']++;
                $data['weekend_total'] += $dailyPrice;
                $data['weekend_price_satuan'] = $dailyPrice;
            } else {
                $data['weekday_count']++;
                $data['weekday_total'] += $dailyPrice;
                $data['weekday_price_satuan'] = $dailyPrice;
            }
            $data['total_price'] += $dailyPrice;
        }
        
        if ($data['total_price'] == 0) {
            $days = $start->diffInDays($end) ?: 1;
            $data['weekday_count'] = $days;
            $data['weekday_total'] = $room->price * $days;
            $data['total_price']   = $room->price * $days;
        }
        
        return $data;
    }
}
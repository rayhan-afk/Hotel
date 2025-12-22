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

    public function confirmation(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) $dayDifference = 1;

        // Hitung Harga Kamar (Sultan Mode)
        $roomPriceTotal = $this->calculateRoomCost($room, $customer, $stayFrom, $stayUntil);

        $tax = $roomPriceTotal * 0.10;
        $downPayment = $roomPriceTotal + $tax; 
        $countPerson = $request->input('count_person', 1);

        return view('transaction.reservation.confirmation', [
            'customer' => $customer,
            'room' => $room,
            'stayFrom' => $stayFrom,
            'stayUntil' => $stayUntil,
            'downPayment' => $downPayment, 
            'dayDifference' => $dayDifference,
            'minimumTax' => $tax,
            'countPerson' => $countPerson,
            'roomPriceTotal' => $roomPriceTotal,
            'breakfastPrice' => self::BREAKFAST_PRICE 
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
            // Transaction null karena belum disimpan
            'transaction' => null 
        ];

        return view('transaction.reservation.invoice_preview', $invoiceData);
    }
    
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
            ->with('success', 'Reservasi Berhasil! Tamu akan otomatis Check-In jam 14:00 hari H.');
    }

    private function getOccupiedRoomID($checkIn, $checkOut)
    {
        return Transaction::where(function($query) use ($checkIn, $checkOut) {
                $query->where('check_in', '<', $checkOut)
                      ->where('check_out', '>', $checkIn);
            })
            // [FIX BUG]
            // Jangan pakai: ->where('status', '!=', 'Done')
            // Karena 'Canceled' pun akan dianggap kamar terisi.
            
            // GANTI DENGAN INI:
            // Hanya anggap kamar terisi jika statusnya 'Reservation' (Booking) atau 'Check In' (Sedang Menginap)
            ->whereIn('status', ['Reservation', 'Check In']) 
            
            ->pluck('room_id');
    }

    // Fungsi Hitung Harga Dinamis
    // Fungsi Hitung Harga Dinamis (FIXED: START OF DAY & SAFETY NET)
    private function calculateRoomCost($room, $customer, $checkIn, $checkOut)
    {
        try {
            // [FIX] Tambahkan startOfDay() agar jam checkin/checkout tidak merusak loop
            $start = Carbon::parse($checkIn)->startOfDay();
            $end   = Carbon::parse($checkOut)->startOfDay();
            
            // Loop per hari
            $period = CarbonPeriod::create($start, $end->copy()->subDay());
            
            $customerGroup = $customer->customer_group ?? 'WalkIn';
            
            $specialPrice = TypePrice::where('type_id', $room->type_id)
                                     ->where('customer_group', $customerGroup)
                                     ->first();

            $totalPrice = 0;
            $daysCount = 0; // Untuk cek apakah loop berjalan

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

            // [SAFETY NET] Jika karena suatu alasan loop tidak jalan (total 0),
            // Hitung manual berdasarkan selisih hari x harga dasar
            if ($totalPrice == 0 || $daysCount == 0) {
                $diff = $start->diffInDays($end);
                if ($diff < 1) $diff = 1;
                return $room->price * $diff;
            }

            return $totalPrice;

        } catch (\Exception $e) {
            // Fallback terakhir jika error sistem
            $diff = Helper::getDateDifference($checkIn, $checkOut);
            if ($diff < 1) $diff = 1;
            return $room->price * $diff;
        }
    }

    // === [FIX 2] PRINT INVOICE LENGKAP & VALID ===
    // ===============================================================
    // METHOD 2: PRINT INVOICE (UNTUK LAPORAN / AKHIR)
    // ===============================================================
    public function printInvoice(Transaction $transaction)
    {
        $transaction->load(['customer', 'room', 'user']);

        $days = Helper::getDateDifference($transaction->check_in, $transaction->check_out);
        if ($days < 1) $days = 1;

        // --- LOGIK HITUNG WEEKDAY/WEEKEND ---
        $calc = $this->calculateDetailPrice($transaction->room, $transaction->customer, $transaction->check_in, $transaction->check_out);

        // Ambil data keuangan dari DB agar presisi
        $grandTotal = $transaction->total_price;
        $subTotal = $grandTotal / 1.1; 
        $tax = $grandTotal - $subTotal;

        // Hitung Sarapan
        // A. Sarapan Utama (Per Hari)
        $breakfastPrice = 0;
        if ($transaction->breakfast == 'Yes') {
            $breakfastPrice = 100000 * $days;
        }

        // B. Extra Bed & Breakfast
        // [FIX] Extra Bed jadi FLAT (Hapus pengali $days)
        $extraBedPrice = ($transaction->extra_bed ?? 0) * 200000; 
        
        // Extra Breakfast tetap PER HARI (Tetap ada pengali $days)
        $extraBreakfastPrice = ($transaction->extra_breakfast ?? 0) * 125000 * $days;

        // 5. Hitung Harga Kamar Murni
        $roomPriceTotal = $subTotal - $breakfastPrice - $extraBedPrice - $extraBreakfastPrice;
        
        $invoiceData = [
            'customer' => $transaction->customer,
            'room' => $transaction->room,
            'check_in' => $transaction->check_in,
            'check_out' => $transaction->check_out,
            'days' => $days,
            'breakfast_status' => $transaction->breakfast,
            
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
            
            'transaction_code' => 'INV-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($transaction->created_at)->format('Y-m-d'),
            'user_name' => $transaction->user->name ?? 'Admin',
            'transaction' => $transaction,
        ];

        return view('transaction.reservation.invoice_preview', $invoiceData);
    }

    // ===============================================================
    // HELPER: FUNGSI HITUNG RINCIAN (Agar coding tidak berulang)
    // ===============================================================
    private function calculateDetailPrice($room, $customer, $checkIn, $checkOut)
    {
        // [FIX] Tambahkan startOfDay() agar jam tidak bikin bug loop
        $start = Carbon::parse($checkIn)->startOfDay();
        $end   = Carbon::parse($checkOut)->startOfDay();
        
        // Loop range tanggal
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
        
        // [SAFETY NET] Jika loop gagal (misal 0 hari), paksa hitung minimal 1 hari (Weekday default)
        if ($data['total_price'] == 0) {
            $days = $start->diffInDays($end) ?: 1;
            $data['weekday_count'] = $days;
            $data['weekday_total'] = $room->price * $days;
            $data['total_price']   = $room->price * $days;
        }
        
        return $data;
    }
}
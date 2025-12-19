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
        // Agar admin melihat harga ASLI (termasuk weekend/diskon) sebelum memilih
        $rooms->getCollection()->transform(function ($room) use ($customer, $stayFrom, $stayUntil, $dayDifference) {
            
            // Panggil fungsi calculateRoomCost
            $totalPrice = $this->calculateRoomCost($room, $customer, $stayFrom, $stayUntil);
            
            // Masukkan hasil hitungan ke object room sebagai atribut tambahan
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

    public function previewInvoice(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) $dayDifference = 1;

        $breakfast = $request->query('breakfast', 'No');

        // Hitung Harga Kamar (Sultan Mode)
        $roomPriceTotal = $this->calculateRoomCost($room, $customer, $stayFrom, $stayUntil);
        
        // Hitung Sarapan
        $breakfastPrice = ($breakfast === 'Yes') ? (self::BREAKFAST_PRICE * $dayDifference) : 0;
        
        $subTotal   = $roomPriceTotal + $breakfastPrice;
        $tax        = $subTotal * 0.10; 
        $grandTotal = $subTotal + $tax;

        $dateCode = Carbon::now()->format('dmY');
        $nextId = Transaction::count() + 1; 
        $transactionCode = $dateCode . '-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $invoiceData = [
            'customer' => $customer,
            'room' => $room,
            'check_in' => $stayFrom,
            'check_out' => $stayUntil,
            'days' => $dayDifference,
            'breakfast_status' => $breakfast,
            'room_price_total' => $roomPriceTotal,
            'breakfast_price_total' => $breakfastPrice,
            'sub_total' => $subTotal, // Tambahkan subtotal agar view aman
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'transaction_code' => $transactionCode,
            'date' => Carbon::now()->format('Y-m-d'),
            'user_name' => auth()->user()->name
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
            ->where('status', '!=', 'Done') 
            ->pluck('room_id');
    }

    // Fungsi Hitung Harga Dinamis
    private function calculateRoomCost($room, $customer, $checkIn, $checkOut)
    {
        try {
            $start = Carbon::parse($checkIn);
            $end = Carbon::parse($checkOut);
            
            // Loop per hari
            $period = CarbonPeriod::create($start, $end->copy()->subDay());
            
            $customerGroup = $customer->customer_group ?? 'General';
            
            $specialPrice = TypePrice::where('type_id', $room->type_id)
                                     ->where('customer_group', $customerGroup)
                                     ->first();

            $totalPrice = 0;

            foreach ($period as $date) {
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

            return $totalPrice;

        } catch (\Exception $e) {
            $diff = Helper::getDateDifference($checkIn, $checkOut);
            if ($diff < 1) $diff = 1;
            return $room->price * $diff;
        }
    }

    // === [FIX 2] PRINT INVOICE LENGKAP ===
    public function printInvoice(Transaction $transaction)
    {
        // Pastikan load relasi
        $transaction->load(['customer', 'room', 'user']);

        $days = Helper::getDateDifference($transaction->check_in, $transaction->check_out);
        if ($days < 1) $days = 1;

        // --- REVERSE ENGINEERING UNTUK TAMPILAN ---
        // Karena di DB cuma simpan 'total_price' (Grand Total), 
        // kita perlu hitung mundur komponennya agar invoice terlihat detail.
        
        $grandTotal = $transaction->total_price;
        $subTotal = $grandTotal / 1.1; // Asumsi Tax 10%
        $tax = $grandTotal - $subTotal;

        // Pisahkan Harga Sarapan & Kamar
        $breakfastPrice = 0;
        if ($transaction->breakfast == 'Yes' || $transaction->breakfast == 1) {
            $breakfastPrice = self::BREAKFAST_PRICE * $days;
        }

        // Sisanya adalah Harga Kamar Total
        $roomPriceTotal = $subTotal - $breakfastPrice;

        $invoiceData = [
            'customer' => $transaction->customer,
            'room' => $transaction->room,
            'check_in' => $transaction->check_in,
            'check_out' => $transaction->check_out,
            'days' => $days,
            
            'breakfast_status' => $transaction->breakfast,
            
            // Rincian Biaya (Hasil Hitung Mundur)
            'room_price_total' => $roomPriceTotal,
            'breakfast_price_total' => $breakfastPrice,
            'sub_total' => $subTotal,
            'tax' => $tax,
            'grand_total' => $grandTotal, // <--- Ini yang paling Valid dari DB
            
            'transaction_code' => 'INV-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($transaction->created_at)->format('Y-m-d'),
            'user_name' => $transaction->user->name ?? 'Admin'
        ];

        return view('transaction.reservation.invoice_preview', $invoiceData);
    }
}
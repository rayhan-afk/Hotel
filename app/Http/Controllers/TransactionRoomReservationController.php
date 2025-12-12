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
use App\Models\User;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Repositories\Interface\RoomRepositoryInterface;
use App\Repositories\Interface\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionRoomReservationController extends Controller
{
    private $customerRepository;
    private $roomRepository;
    private $transactionRepository;

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

        return view('transaction.reservation.chooseRoom', [
            'customer' => $customer,
            'rooms' => $rooms,
            'stayFrom' => $stayFrom,
            'stayUntil' => $stayUntil,
            'roomsCount' => $roomsCount,
        ]);
    }

    public function confirmation(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) {
            $dayDifference = 1;
        }

        $roomPriceTotal = $room->price * $dayDifference;
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
            'countPerson' => $countPerson
        ]);
    }

    // ==========================================
    // [UPDATE] Preview Invoice dengan No. Urut
    // ==========================================
    public function previewInvoice(Customer $customer, Room $room, $stayFrom, $stayUntil, Request $request)
    {
        $dayDifference = Helper::getDateDifference($stayFrom, $stayUntil);
        if ($dayDifference < 1) $dayDifference = 1;

        $breakfast = $request->query('breakfast', 'No');

        $roomPriceTotal = $room->price * $dayDifference;
        $breakfastPrice = ($breakfast === 'Yes') ? (140000 * $dayDifference) : 0;
        
        $subTotal   = $roomPriceTotal + $breakfastPrice;
        $tax        = $subTotal * 0.10; 
        $grandTotal = $subTotal + $tax;

        // [LOGIKA BARU] Generate Nomor: TanggalHariIni - UrutanTransaksiBerikutnya
        // Contoh: 09122025-105
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
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'transaction_code' => $transactionCode, // Menggunakan format baru
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

        $roomPriceTotal = $room->price * $dayDifference;
        $breakfastPrice = ($request->breakfast === 'Yes') ? (140000 * $dayDifference) : 0;
        
        $subTotal       = $roomPriceTotal + $breakfastPrice;
        $tax            = $subTotal * 0.10; 
        $grandTotal     = $subTotal + $tax;

        $request->merge([
            'total_price' => $grandTotal,
            'paid_amount' => $grandTotal,
            'status'      => 'Reservation' 
        ]);

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
}
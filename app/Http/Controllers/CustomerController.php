<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest; // Pastikan ini ada jika dipakai
use App\Models\Customer;
use App\Models\User;
use App\Repositories\Interface\CustomerRepositoryInterface;
use App\Repositories\Interface\ImageRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Constructor Injection
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function index(Request $request)
    {
        // [UPDATE] Jika Request adalah AJAX (dari Datatable JS), kembalikan JSON
        if ($request->ajax()) {
            return response()->json($this->customerRepository->getCustomersDatatable($request));
        }

        // Jika bukan AJAX, kembalikan halaman HTML biasa (tanpa data awal)
        return view('customer.index');
    }

    public function create()
    {
        return view('customer.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerRepository->store($request);

        return redirect()->route('customer.index')
            ->with('success', 'Customer ' . $customer->name . ' berhasil ditambahkan!');
    }

    public function show(Customer $customer)
{
    // Load relasi user, transactions, room, dan type agar tidak N+1 Problem
    $customer->load(['user', 'transactions.room.type']);
    
    return view('customer.show', ['customer' => $customer]);
}

    public function edit(Customer $customer)
    {
        return view('customer.edit', ['customer' => $customer]);
    }

    // Gunakan UpdateCustomerRequest jika sudah dibuat, atau StoreCustomerRequest jika disatukan
    public function update(Customer $customer, StoreCustomerRequest $request)
    {
        // Gunakan repository untuk update agar logic user & customer terhandle
        $this->customerRepository->update($customer, $request);

        // Return JSON success agar ditangkap JS Swal
        if ($request->ajax()) {
            return response()->json(['message' => 'Data customer berhasil diperbarui!']);
        }

        // Fallback jika tidak pakai JS
        return redirect()->route('customer.index')
            ->with('success', 'Data customer berhasil diperbarui!');
    }

    public function destroy(Customer $customer, ImageRepositoryInterface $imageRepository)
    {
        try {
            // Ambil user terkait sebelum customer dihapus
            $user = User::find($customer->user_id);
            
            // Path folder gambar: img/user/slug(name)-id
            $folderName = Str::slug($user->name) . '-' . $user->id;
            $avatar_path = public_path('img/user/' . $folderName);

            // Hapus data
            $customer->delete();
            if($user) $user->delete();

            // Hapus folder gambar jika ada
            if (is_dir($avatar_path)) {
                $imageRepository->destroy($avatar_path);
            }

            return response()->json(['message' => 'Customer berhasil dihapus!']);
        } catch (\Exception $e) {
            // Cek kode error Constraint Violation (Data masih dipakai di transaksi)
            $errorMessage = 'Gagal menghapus data.';
            if (isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000') {
                $errorMessage = 'Data tidak bisa dihapus karena masih terhubung dengan transaksi lain.';
            }

            // Return Error JSON agar ditangkap SweetAlert
            return response()->json(['message' => $errorMessage], 500);
        }
    }
}
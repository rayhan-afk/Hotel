<?php

namespace App\Repositories\Implementation;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\Interface\CustomerRepositoryInterface;
use Illuminate\Support\Str;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Method baru khusus untuk Datatable Server-side
     */
    public function getCustomersDatatable($request)
    {
        // 1. Query Dasar (Gunakan JOIN ke users agar bisa ambil & cari Email/Avatar)
        $query = Customer::query()
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->select('customers.*', 'users.email as user_email', 'users.avatar as user_avatar');

        // 2. Filter Pencarian Global (Nama, HP, Pekerjaan, Alamat, Email, dan Grup)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")       // Cari Nama
                  ->orWhere('customers.phone', 'LIKE', "%{$search}%")    // Cari No HP
                  ->orWhere('customers.job', 'LIKE', "%{$search}%")      // Cari Pekerjaan
                  ->orWhere('customers.address', 'LIKE', "%{$search}%")  // Cari Alamat
                  ->orWhere('customers.customer_group', 'LIKE', "%{$search}%") // [BARU] Cari Grup
                  ->orWhere('users.email', 'LIKE', "%{$search}%");       // Cari Email
            });
        }

        // 3. Sorting (Sesuai urutan kolom di JS customer.js)
        // [PENTING] Urutan index array ini harus sama persis dengan 'columns' di Javascript
        $columns = [
            0 => 'customers.id',      // Kolom No
            1 => 'customers.user_id', // Kolom Avatar (Sorting by user ID)
            2 => 'customers.name',    // Kolom Nama
            3 => 'customers.customer_group', // [BARU] Kolom Grup
            4 => 'customers.phone',   // Kolom Kontak
            5 => 'customers.job',     // Kolom Pekerjaan
            6 => 'customers.address', // Kolom Alamat
            7 => 'customers.id'       // Kolom Aksi
        ];

        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'customers.id';
        
        $query->orderBy($orderColumn, $orderDir);

        // 4. Hitung Total Data
        $totalData = Customer::count();
        $totalFiltered = $query->count();

        // 5. Pagination
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);

        $data = $query->offset($start)->limit($limit)->get();

        // Load relasi user sebagai fallback
        $data->load('user');

        // 6. Return Format JSON
        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data
        ];
    }

    public function store($request)
    {
        // 1. Logic Email & Password
        $email = $request->email;
        if (empty($email)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $email = $cleanPhone . '@sawunggaling.hotel'; 
        }

        $password = $request->birthdate ? bcrypt($request->birthdate) : bcrypt(Str::random(10));

        // 2. Buat User Baru
        $user = User::create([
            'name'       => $request->name,
            'email'      => $email,
            'password'   => $password,
            'role'       => 'Customer',
            'random_key' => Str::random(60),
        ]);

        // 3. Handle Upload Foto (Avatar)
        if ($request->hasFile('avatar')) {
            $folderName = $user->id . '-' . Str::slug($user->name);
            $destinationPath = public_path('img/user/' . $folderName);
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file = $request->file('avatar');
            $fileName = $file->getClientOriginalName();
            $file->move($destinationPath, $fileName);

            $user->avatar = $fileName;
            $user->save();
        }

        // 4. Buat Customer Baru ([FIX] Tambahkan customer_group)
        $customer = Customer::create([
            'name'           => $request->name, 
            'address'        => $request->address,
            'job'            => $request->job,
            'birthdate'      => $request->birthdate, 
            'gender'         => $request->gender,
            'phone'          => $request->phone,
            'customer_group' => $request->customer_group ?? 'General', // <--- PENTING!
            'user_id'        => $user->id
        ]);

        return $customer;
    }

    public function update($customer, $request)
    {
        // 1. Update Data User (Email & Avatar)
        $user = $customer->user;
        
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->hasFile('avatar')) {
            $folderName = $user->id . '-' . Str::slug($user->name);
            $destinationPath = public_path('img/user/' . $folderName);
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file = $request->file('avatar');
            $fileName = $file->getClientOriginalName();
            $file->move($destinationPath, $fileName);

            $user->avatar = $fileName;
        }
        
        $user->name = $request->name;
        $user->save();

        // 2. Update Data Customer ([FIX] Tambahkan customer_group)
        $customer->update([
            'name'           => $request->name,
            'phone'          => $request->phone,
            'job'            => $request->job,
            'birthdate'      => $request->birthdate,
            'gender'         => $request->gender,
            'address'        => $request->address,
            'customer_group' => $request->customer_group ?? 'General', // <--- PENTING!
        ]);

        return $customer;
    }

    // === METHOD LAMA (BIARKAN SAJA) ===

    public function getCustomers($request)
    {
        if($request->has('q')) {
            $search = $request->q;
            return Customer::where('name', 'LIKE', "%$search%")
                           ->orWhere('phone', 'LIKE', "%$search%")
                           ->paginate(10);
        }
        return Customer::with('user')->paginate(10);
    }

    public function get($request)
    {
        return Customer::with('user')->orderBy('id', 'DESC')
            ->when($request->q, function ($query) use ($request) {
                $query->where('name', 'Like', '%'.$request->q.'%')
                    ->orWhere('id', 'Like', '%'.$request->q.'%');
            })
            ->paginate(8)
            ->appends($request->all());
    }

    public function count($request)
    {
        return Customer::with('user')->orderBy('id', 'DESC')
            ->when($request->q, function ($query) use ($request) {
                $query->where('name', 'Like', '%'.$request->q.'%')
                    ->orWhere('id', 'Like', '%'.$request->q.'%');
            })
            ->count();
    }
}
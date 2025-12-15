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
        // Kita gunakan select('customers.*') agar ID utama tetap dari tabel customers
        $query = Customer::query()
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->select('customers.*', 'users.email as user_email', 'users.avatar as user_avatar');

        // 2. Filter Pencarian Global (Nama, HP, Pekerjaan, Alamat, Email)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('customers.name', 'LIKE', "%{$search}%")       // Cari Nama
                  ->orWhere('customers.phone', 'LIKE', "%{$search}%")    // Cari No HP
                  ->orWhere('customers.job', 'LIKE', "%{$search}%")      // Cari Pekerjaan
                  ->orWhere('customers.address', 'LIKE', "%{$search}%")  // [BARU] Cari Alamat
                  ->orWhere('users.email', 'LIKE', "%{$search}%");       // [BARU] Cari Email
            });
        }

        // 3. Sorting (Sesuai kolom yang diklik di Datatable)
        // Urutan array ini harus sama dengan urutan 'columns' di file JS (customer.js)
        $columns = [
            0 => 'customers.id',      // Kolom No
            1 => 'customers.user_id', // Kolom Avatar
            2 => 'customers.name',    // Kolom Nama
            3 => 'customers.phone',   // Kolom Kontak
            4 => 'customers.job',     // Kolom Pekerjaan
            5 => 'customers.address', // Kolom Alamat
            6 => 'customers.id'       // Kolom Aksi
        ];

        $orderColumnIndex = $request->input('order.0.column', 0); // Ambil index kolom yang diklik
        $orderDir = $request->input('order.0.dir', 'desc');       // Ambil arah sort (asc/desc)
        $orderColumn = $columns[$orderColumnIndex] ?? 'customers.id'; // Default sort by ID
        
        $query->orderBy($orderColumn, $orderDir);

        // 4. Hitung Total Data (Penting untuk Pagination)
        $totalData = Customer::count(); // Total semua data di DB
        $totalFiltered = $query->count(); // Total data setelah difilter search

        // 5. Pagination (Limit & Offset)
        $limit = $request->input('length', 10); // Berapa data per halaman
        $start = $request->input('start', 0);   // Mulai dari data ke berapa

        $data = $query->offset($start)->limit($limit)->get();

        // Load relasi user sebagai fallback (jika ada properti lain di model yang butuh)
        $data->load('user');

        // 6. Return Format JSON Standar Datatable
        return [
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data
        ];
    }

    public function store($request)
    {
        // 1. Logic Email Opsional & Password
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

        // 4. Buat Customer Baru
        $customer = Customer::create([
            'name'      => $request->name, 
            'address'   => $request->address,
            'job'       => $request->job,
            'birthdate' => $request->birthdate, 
            'gender'    => $request->gender,
            'phone'     => $request->phone, 
            'user_id'   => $user->id
        ]);

        return $customer;
    }

    // Method getCustomers() lama biarkan saja jika masih dipakai fitur lain
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

    // Method get & count lama biarkan saja
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

    public function update($customer, $request)
    {
        // 1. Update Data User (Email & Avatar)
        $user = $customer->user;
        
        // Update Email jika ada input email
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Update Avatar jika ada file baru
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika bukan default (Opsional, implementasikan ImageRepo jika mau)
            // ... logic hapus ...

            // Upload Avatar Baru
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
        
        // Simpan perubahan User (Nama juga update di user agar sinkron)
        $user->name = $request->name;
        $user->save();

        // 2. Update Data Customer
        $customer->update([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'job'       => $request->job,
            'birthdate' => $request->birthdate,
            'gender'    => $request->gender,
            'address'   => $request->address,
        ]);

        return $customer;
    }
}
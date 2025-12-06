<?php

namespace App\Repositories\Implementation;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\Interface\CustomerRepositoryInterface;
use Illuminate\Support\Str;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function getCustomers($request)
    {
        // Query untuk pencarian customer
        if($request->has('q')) {
            $search = $request->q;
            return Customer::where('name', 'LIKE', "%$search%")
                        ->orWhere('phone', 'LIKE', "%$search%") // Bisa cari by HP
                        ->paginate(10);
        }
        
        return Customer::with('user')->paginate(10);
    }

    public function store($request)
    {
        // 1. Logic Email Opsional & Password
        // Jika email kosong, generate email dummy dari no HP agar User tetap bisa dibuat
        $email = $request->email;
        if (empty($email)) {
            // Bersihkan no HP dari karakter non-angka
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $email = $cleanPhone . '@sawunggaling.hotel'; 
        }

        // Gunakan tanggal lahir sebagai password default jika ada, atau random
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
        // Simpan avatar setelah user dibuat agar bisa pakai ID/Nama user untuk nama file (opsional)
        if ($request->hasFile('avatar')) {
            // Gunakan path yang rapi, misal: img/user/{id}-{nama}
            $folderName = $user->id . '-' . Str::slug($user->name);
            $destinationPath = public_path('img/user/' . $folderName);
            
            // Pastikan folder ada
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file = $request->file('avatar');
            $fileName = $file->getClientOriginalName();
            $file->move($destinationPath, $fileName);

            // Update kolom avatar di user
            $user->avatar = $fileName;
            $user->save();
        }

        // 4. Buat Customer Baru
        $customer = Customer::create([
            'name'      => $request->name, // Bisa ambil dari $user->name atau request
            'address'   => $request->address,
            'job'       => $request->job,
            'birthdate' => $request->birthdate, // Bisa null
            'gender'    => $request->gender,
            'phone'     => $request->phone, // Simpan No HP
            'user_id'   => $user->id
        ]);

        return $customer;
    }

    // ... method lain (get, count, update, delete) biarkan seperti aslinya atau sesuaikan jika perlu
    // Contoh implementasi get/count yang ada di kode lama Anda:
    
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
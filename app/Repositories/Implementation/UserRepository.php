<?php

namespace App\Repositories\Implementation;

use App\Models\User;
use App\Repositories\Interface\UserRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    public function store($userData)
    {
        $user = new User;
        $user->name = $userData->name;
        $user->email = $userData->email;
        $user->password = bcrypt($userData->password);
        $user->role = $userData->role;
        $user->random_key = Str::random(60);
        $user->save();

        return $user;
    }

    public function showUser($request)
    {
        $query = User::whereIn('role', ['Super', 'Admin', 'Manager', 'Dapur', 'Housekeeping', 'Kasir'])
            ->orderBy('id', 'DESC');
        
        // Debug: Log parameter yang masuk
        \Log::info('Search User - Parameter qu: ' . ($request->qu ?? 'NULL'));
        \Log::info('Search User - Has qu: ' . ($request->has('qu') ? 'YES' : 'NO'));
        \Log::info('Search User - Empty qu: ' . (empty($request->qu) ? 'YES' : 'NO'));
        
        // Jika ada parameter search 'qu' dan tidak kosong
        if (!empty($request->qu)) {
            $searchTerm = trim($request->qu);
            \Log::info('Search User - Applying filter with term: ' . $searchTerm);
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Debug: Log SQL query
        \Log::info('Search User - SQL: ' . $query->toSql());
        \Log::info('Search User - Bindings: ' . json_encode($query->getBindings()));
        
        return $query->paginate(5, ['*'], 'users')
            ->appends($request->all());
    }

    public function showCustomer($request)
    {
        $query = User::where('role', 'Customer')
            ->orderBy('id', 'DESC');
        
        // Debug: Log parameter yang masuk
        \Log::info('Search Customer - Parameter qc: ' . ($request->qc ?? 'NULL'));
        
        // Jika ada parameter search 'qc' dan tidak kosong
        if (!empty($request->qc)) {
            $searchTerm = trim($request->qc);
            \Log::info('Search Customer - Applying filter with term: ' . $searchTerm);
            
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Debug: Log SQL query
        \Log::info('Search Customer - SQL: ' . $query->toSql());
        \Log::info('Search Customer - Bindings: ' . json_encode($query->getBindings()));
        
        return $query->paginate(5, ['*'], 'customers')
            ->appends($request->all());
    }
}
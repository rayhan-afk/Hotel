<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Aturan validasi umum (untuk Create & Update/Put)
        $rules = [
            'name'      => 'required',
            'email'     => 'nullable|email|unique:users,email', // Email jadi Opsional (nullable)
            'phone'     => 'required|numeric', // No HP Wajib & Angka
            'address'   => 'required|max:255',
            'job'       => 'required',
            'birthdate' => 'nullable|date', // Tanggal Lahir jadi Opsional (nullable)
            'gender'    => 'required|in:Male,Female',
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Avatar juga opsional
        ];

        // Jika method PUT (Update), validasi email harus mengecualikan user saat ini agar tidak error "email has been taken"
        if ($this->isMethod('put')) {
            // Kita perlu ID user untuk pengecualian unique email. 
            // Asumsi: Kita bisa akses $this->user atau parameter route customer.
            // Namun untuk amannya di sini kita biarkan nullable dulu, atau logic unique di controller/repo.
            // Untuk simplifikasi update:
            $rules['email'] = 'nullable|email'; 
        }

        return $rules;
    }
}
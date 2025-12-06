<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session; // <-- TAMBAHKAN INI

class LoginRequest extends FormRequest
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
        return [
            'email' => [
                'required',
                'email', // Sebaiknya tambahkan validasi format email
            ],
            'password' => [
                'required',
            ],
            // <-- TAMBAHKAN ATURAN VALIDASI CAPTCHA DI SINI -->
            'captcha' => [
                'required',
                // Custom rule untuk membandingkan input dengan kode di Session
                function ($attribute, $value, $fail) {
                    // Ambil kode dari session dan bandingkan (CAPITALIZED)
                    // Kita menggunakan strtoupper($value) untuk memastikan perbandingan case-insensitive
                    if (Session::get('captcha_code') !== strtoupper($value)) {
                        $fail('Kode Keamanan yang Anda masukkan salah.');
                    }
                    // Setelah dicek, hapus kode lama dari session untuk keamanan
                    Session::forget('captcha_code');
                },
            ],
        ];
    }
}
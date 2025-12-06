<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRuangRapatPaketRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'isi_paket' => 'required|string',
            'fasilitas' => 'required|string',
            'harga' => 'required|numeric|min:0',
        ];
    }
}
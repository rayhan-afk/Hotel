<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $roomId = $this->room ? $this->room->id : null;

        return [
            'type_id' => 'required|exists:types,id',
            'number' => 'required|string|max:255|unique:rooms,number,' . $roomId,
            'name' => 'required|string|max:255',
            'capacity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0', 
            'room_facilities' => 'nullable|string',
            'bathroom_facilities' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi gambar
        ];
    }
}
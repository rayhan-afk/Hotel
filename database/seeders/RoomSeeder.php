<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // Data Dummy Manual yang Lebih Rapih
        Room::create([
            'type_id' => 1, // Pastikan ID 1 ada di tabel types (Superior)
            'number' => '101',
            'name' => 'Superior A',
            'capacity' => 2,
            'price' => 350000,
            'area_sqm' => 24,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, WiFi, TV 32 Inch',
            'bathroom_facilities' => 'Hot Water, Shower',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '102',
            'name' => 'Superior B',
            'capacity' => 2,
            'price' => 350000,
            'area_sqm' => 24,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, WiFi, TV 32 Inch',
            'bathroom_facilities' => 'Hot Water, Shower',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 2, // Pastikan ID 2 ada (Deluxe)
            'number' => '201',
            'name' => 'Deluxe View',
            'capacity' => 2,
            'price' => 500000,
            'area_sqm' => 30,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, WiFi, TV 40 Inch, Mini Fridge',
            'bathroom_facilities' => 'Bathtub, Hair Dryer',
            'main_image_path' => 'img/default/default-room.png'
        ]);
    }
}
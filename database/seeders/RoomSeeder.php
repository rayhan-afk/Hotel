<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // Deluxe Room
        Room::create([
            'type_id' => 1, // Deluxe
            'number' => '101',
            'name' => 'Deluxe Garden View',
            'capacity' => 2,
            'price' => 450000,
            'area_sqm' => 15.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Superior Room
        Room::create([
            'type_id' => 2, // Superior
            'number' => '103',
            'name' => 'Superior Pool View',
            'capacity' => 2,
            'price' => 550000,
            'area_sqm' => 27.7,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Sawunggaling Suite
        Room::create([
            'type_id' => 3, // Sawunggaling Suite
            'number' => '401',
            'name' => 'Sawunggaling Suite',
            'capacity' => 4,
            'price' => 1500000,
            'area_sqm' => 47.7,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, 2 Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Living Room, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Pitaloka
        Room::create([
            'type_id' => 4, // Pitaloka
            'number' => '402',
            'name' => 'Pitaloka Suite',
            'capacity' => 3,
            'price' => 1200000,
            'area_sqm' => 34.2,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Boscha
        Room::create([
            'type_id' => 5, // Boscha
            'number' => '403',
            'name' => 'Boscha Suite',
            'capacity' => 2,
            'price' => 950000,
            'area_sqm' => 25.5,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Priangan
        Room::create([
            'type_id' => 6, // Priangan
            'number' => '404',
            'name' => 'Priangan Suite',
            'capacity' => 2,
            'price' => 1000000,
            'area_sqm' => 27,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);
    }
}
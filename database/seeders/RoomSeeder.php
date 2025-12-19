<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // DELUXE ROOMS (type_id = 1)
        
        // Floor 1 - Deluxe
        Room::create([
            'type_id' => 1,
            'number' => '101',
            'name' => 'Deluxe Room 101',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 15.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '102',
            'name' => 'Deluxe Room 102',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 20.9,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '103',
            'name' => 'Deluxe Room 103',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 27.7,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Water Heater, Balkon',
            'bathroom_facilities' => 'Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '104',
            'name' => 'Deluxe Room 104',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 24.7,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Floor 2 - Deluxe
        Room::create([
            'type_id' => 1,
            'number' => '201',
            'name' => 'Deluxe Room 201',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '202',
            'name' => 'Deluxe Room 202',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '203',
            'name' => 'Deluxe Room 203',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '204',
            'name' => 'Deluxe Room 204',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 27.6,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '205',
            'name' => 'Deluxe Room 205',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '206',
            'name' => 'Deluxe Room 206',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '207',
            'name' => 'Deluxe Room 207',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '208',
            'name' => 'Deluxe Room 208',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 27.2,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Floor 3 - Deluxe
        Room::create([
            'type_id' => 1,
            'number' => '301',
            'name' => 'Deluxe Room 301',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '302',
            'name' => 'Deluxe Room 302',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '303',
            'name' => 'Deluxe Room 303',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 25.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Meja, Smart TV',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '304',
            'name' => 'Deluxe Room 304',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '305',
            'name' => 'Deluxe Room 305',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        Room::create([
            'type_id' => 1,
            'number' => '306',
            'name' => 'Deluxe Room 306',
            'capacity' => 2,
            'price' => 800000,
            'area_sqm' => 21.4,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // SUITE ROOMS (type_id = 2)
        
        // Pitaloka Suite
        Room::create([
            'type_id' => 2,
            'number' => '402',
            'name' => 'Pitaloka Suite',
            'capacity' => 3,
            'price' => 1000000,
            'area_sqm' => 34.2,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Minibar, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Boscha Suite
        Room::create([
            'type_id' => 2,
            'number' => '403',
            'name' => 'Boscha Suite',
            'capacity' => 2,
            'price' => 1000000,
            'area_sqm' => 25.5,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Mini bar, Water Kettle, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // Priangan Suite
        Room::create([
            'type_id' => 2,
            'number' => '404',
            'name' => 'Priangan Suite',
            'capacity' => 2,
            'price' => 1000000,
            'area_sqm' => 27,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Mini bar, Smart TV, Meja, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);

        // PRESIDENT SUITE (type_id = 3)
        
        // Sawunggaling President Suite
        Room::create([
            'type_id' => 3,
            'number' => '401',
            'name' => 'Sawunggaling President Suite',
            'capacity' => 4,
            'price' => 1150000,
            'area_sqm' => 47.7,
            'breakfast' => 'Yes',
            'room_facilities' => 'AC, Coffee & Tea Set, Water Kettle, Mini bar, 2 Smart TV, Meja, Living Room, Balkon',
            'bathroom_facilities' => 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)',
            'main_image_path' => 'img/default/default-room.png'
        ]);
    }
}
<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Pastikan Type sudah ada, atau buat baru jika kosong
            'type_id' => Type::inRandomOrder()->first()->id ?? Type::factory(),
            
            // Kolom Wajib
            'number' => $this->faker->unique()->numberBetween(100, 999),
            'name' => 'Room ' . $this->faker->randomElement(['Rose', 'Tulip', 'Orchid', 'Jasmine']),
            'capacity' => $this->faker->numberBetween(1, 4),
            'price' => $this->faker->numberBetween(100, 1000) * 1000, // Harga kelipatan ribuan
            
            // Kolom Baru (Nullable/Default)
            'area_sqm' => $this->faker->numberBetween(20, 60),
            'breakfast' => $this->faker->randomElement(['Yes', 'No']),
            'room_facilities' => $this->faker->sentence(10), // Ganti lorem ipsum panjang jadi kalimat pendek
            'bathroom_facilities' => $this->faker->sentence(10),
            'main_image_path' => 'img/default/default-room.png',
            
            // HAPUS kolom 'view' dan 'room_status_id' yang bikin error
        ];
    }
}
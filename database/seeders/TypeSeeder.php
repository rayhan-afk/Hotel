<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = [
            'Deluxe',
            'Superior',
            'Sawunggaling Suite',
            'Pitaloka',
            'Boscha',
            'Priangan',
        ];

        $information = [
            'Kamar Deluxe menawarkan kenyamanan dengan ukuran kamar yang bervariasi mulai dari 15.4 m² hingga 27.6 m². Dilengkapi dengan fasilitas modern seperti AC, Smart TV, Coffee & Tea Set, serta balkon di beberapa kamar. Kamar mandi dilengkapi dengan water heater, shower, handuk, dan amenities lengkap (shampoo, sabun, dental kit, slipper). Cocok untuk tamu yang mencari kenyamanan dengan harga terjangkau.',
            'Kamar Superior merupakan pilihan premium dengan ukuran kamar berkisar 21.4 m² hingga 27.7 m². Menawarkan fasilitas yang lebih lengkap dengan AC, Smart TV, Coffee & Tea Set, water kettle, meja kerja, dan balkon yang menghadirkan pemandangan indah. Kamar mandi dilengkapi dengan water heater, shower berkualitas, handuk premium, dan amenities lengkap. Ideal untuk tamu yang menginginkan pengalaman menginap yang lebih eksklusif.',
            'Suite termewah dengan luas 47.7 m² yang menawarkan pengalaman menginap istimewa. Dilengkapi dengan living room terpisah, 2 Smart TV, minibar, AC, coffee & tea set, water kettle, dan meja kerja. Kamar mandi mewah dengan water heater, shower premium, handuk berkualitas tinggi, dan amenities lengkap. Balkon private memberikan pemandangan spektakuler. Sempurna untuk tamu VIP atau pasangan yang merayakan momen spesial.',
            'Kamar Pitaloka seluas 34.2 m² menghadirkan kombinasi sempurna antara kemewahan dan kenyamanan. Dilengkapi dengan AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja yang luas. Kamar mandi premium dengan water heater, shower berkualitas, handuk lembut, dan amenities lengkap. Balkon private untuk bersantai sambil menikmati pemandangan. Pilihan ideal untuk pengalaman menginap yang berkesan.',
            'Kamar Boscha dengan luas 25.5 m² menawarkan kenyamanan modern dengan sentuhan elegan. Fasilitas lengkap meliputi AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja. Kamar mandi dilengkapi dengan water heater, shower, handuk berkualitas, dan amenities lengkap. Balkon private memberikan ruang santai yang nyaman. Cocok untuk tamu bisnis maupun leisure yang menghargai kualitas.',
            'Kamar Priangan seluas 27 m² menggabungkan fungsionalitas dan kenyamanan. Dilengkapi dengan AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja yang praktis. Kamar mandi modern dengan water heater, shower, handuk lembut, dan amenities lengkap. Balkon private menjadi nilai tambah untuk menikmati udara segar. Pilihan tepat untuk pengalaman menginap yang menyenangkan dan berkesan.',
        ];

        for ($i = 0; $i < count($name); $i++) {
            Type::create([
                'name' => $name[$i],
                'information' => $information[$i],
            ]);
        }
    }
}

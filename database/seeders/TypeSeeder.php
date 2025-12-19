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
            'Suite',
            'President Suite',
        ];

        $information = [
            'Kamar Deluxe menawarkan kenyamanan lebih dibanding kamar standar. Dilengkapi dengan fasilitas lengkap seperti tempat tidur nyaman, kamar mandi modern, TV, AC, dan area kerja. Cocok untuk tamu yang menginginkan kenyamanan ekstra dengan harga yang masih terjangkau.',
            'Kamar Suite memiliki ruang yang lebih luas dan biasanya terdiri dari area tidur terpisah dengan ruang tamu. Fasilitasnya lebih premium, seperti sofa, meja tamu, dan interior yang lebih eksklusif. Sangat cocok untuk tamu yang menginap lebih lama atau membutuhkan ruang tambahan untuk bersantai maupun menerima tamu.',
            'Kamar President Suite merupakan tipe kamar paling mewah dengan fasilitas terbaik. Memiliki ruang yang sangat luas, desain interior eksklusif, ruang tamu, kamar tidur terpisah, dan fasilitas premium seperti bathtub, ruang makan, serta layanan khusus. Cocok untuk tamu VIP atau tamu yang menginginkan pengalaman menginap kelas atas.',
        ];

        for ($i = 0; $i < count($name); $i++) {
            Type::create([
                'name' => $name[$i],
                'information' => $information[$i],
            ]);
        }
    }
}

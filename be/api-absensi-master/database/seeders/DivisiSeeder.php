<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Devision;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Devision::create([
            'name' => "Irigasi",
            'slug' => "irigasi",
            'description' => "-",
            'status' => "publish"
        ]);

        Devision::create([
            'name' => "Drainase",
            'slug' => "drainase",
            'description' => "-",
            'status' => "publish"
        ]);
        
        Devision::create([
            'name' => "Jalan",
            'slug' => "jalan",
            'description' => "-",
            'status' => "publish"
        ]);

        Devision::create([
            'name' => "PPJ dan Jakon",
            'slug' => "ppj-dan-jakon",
            'description' => "-",
            'status' => "publish"
        ]);

        Devision::create([
            'name' => "Bangunan dan Gedung",
            'slug' => "bangunan-dan-gedung",
            'description' => "-",
            'status' => "publish"
        ]);

        Devision::create([
            'name' => "Penataan Ruang",
            'slug' => "penataan-ruang",
            'description' => "-",
            'status' => "publish"
        ]);

    }
}

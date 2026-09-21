<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Project::create([
            'devisionId' => 1,
            'userId' => 2,
            'projectNo' => "JLN01",
            'startdate' => '2024-01-01',
            'targetdate' => '2024-01-31',
            'cost' => 40000000,
            'status' => 'publish',
            'rowStatus' => 0,
            'address' => 'jl buah batu',
            'latitude' => '-6.9655493',
            'longtitude' => '107.6379256',
            'name' => 'Jalan Bubat',
            'slug' => 'jalan-bubat',
            'description' => '-'
        ]);
    }
}

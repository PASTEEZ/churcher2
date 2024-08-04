<?php

namespace Database\Seeders\Transport;

use App\SmVehicle;
use Illuminate\Database\Seeder;

class SmVehiclesTableSeeder extends Seeder
{
    public function run($church_id = 1, $count = 5){

        SmVehicle::factory()->times($count)->create([
            'church_id' => $church_id
        ]);
    }

}
<?php

namespace Database\Seeders\Transport;

use App\SmRoute;
use Illuminate\Database\Seeder;

class SmRoutesTableSeeder extends Seeder
{
    public function run($church_id = 1, $church_year_id = 1, $count = 5){
        SmRoute::factory()->times($count)->create([
           'church_id' => $church_id,
           'church_year_id' => $church_year_id,
        ]);
    }

}
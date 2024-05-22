<?php

namespace Database\Seeders\Academics;

use App\SmClassRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmClassRoomsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count = 5)
    {
        SmClassRoom::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id
        ]);

    }
}

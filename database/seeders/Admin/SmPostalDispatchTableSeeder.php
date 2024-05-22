<?php

namespace Database\Seeders\Admin;

use App\SmPostalDispatch;
use Illuminate\Database\Seeder;

class SmPostalDispatchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=10)
    {
        SmPostalDispatch::factory()->times($count)->create([
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ]);
    }
}

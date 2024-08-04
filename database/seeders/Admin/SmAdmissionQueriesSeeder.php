<?php

namespace Database\Seeders\Admin;

use App\SmAdmissionQuery;
use Illuminate\Database\Seeder;

class SmAdmissionQueriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $church_year_id = 1, $count = 10)
    {
        SmAdmissionQuery::factory()->times($count)->create([
            'class' => 1,
            'church_id' => $church_id,
            'church_year_id' => $church_year_id
        ]);

    }
}

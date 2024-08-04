<?php

namespace Database\Seeders\Inventory;

use App\SmItemStore;
use Illuminate\Database\Seeder;

class SmItemStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $school_academic=[
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        SmItemStore::factory()->times($count)->create($school_academic);

    }
}

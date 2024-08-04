<?php

namespace Database\Seeders\Inventory;

use App\SmSupplier;
use Illuminate\Database\Seeder;

class SmSupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        //
        $school_academic=[
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        SmSupplier::factory()->times($count)->create($school_academic);

    }
}

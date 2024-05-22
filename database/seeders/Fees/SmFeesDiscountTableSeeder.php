<?php

namespace Database\Seeders\Fees;

use App\SmFeesDiscount;
use Illuminate\Database\Seeder;

class SmFeesDiscountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        //
        SmFeesDiscount::factory()->times($count)->create([
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id
        ]);
    }
}

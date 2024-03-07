<?php

namespace Database\Seeders\HumanResources;

use Database\Factories\SmHourRateFactory;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class SmHourRateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        SmHourRateFactory::factory()->times($count)->create([
            'church_id'=> $church_id,
            'church_year_id'=> $church_year_id,
        ]);

    }
}

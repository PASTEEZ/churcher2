<?php

namespace Database\Seeders\Academics;

use App\SmSection;
use Illuminate\Database\Seeder;

class SmSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $church_year_id = null, $count = 5)
    {
        SmSection::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id
        ]);
    }
}

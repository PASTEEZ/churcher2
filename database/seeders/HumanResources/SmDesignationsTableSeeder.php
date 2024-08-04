<?php

namespace Database\Seeders\HumanResources;

use App\SmDesignation;
use Illuminate\Database\Seeder;

class SmDesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $count= 1)
    {
        SmDesignation::factory()->times($count)->create([
            'church_id' => $church_id
        ]);
    }
}

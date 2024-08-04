<?php

namespace Database\Seeders\Dormitory;

use App\SmDormitoryList;
use Illuminate\Database\Seeder;

class SmDormitoryListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $count = 5)
    {
        SmDormitoryList::factory()->times($count)->create([
            'church_id' => $church_id
        ]);
    }
}

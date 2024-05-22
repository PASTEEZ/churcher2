<?php

namespace Database\Seeders\Dormitory;

use App\SmRoomList;
use Illuminate\Database\Seeder;

class SmRoomListsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $count = 5)
    {
        SmRoomList::factory()->times($count)->create([
            'church_id' => $church_id
        ]);
    }
}

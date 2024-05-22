<?php

namespace Database\Seeders\Communicate;

use App\SmSendMessage;
use Illuminate\Database\Seeder;

class SmSendMessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=10)
    {
        SmSendMessage::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id,
        ]);
    }
}

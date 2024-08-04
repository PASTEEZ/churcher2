<?php

namespace Database\Seeders\Communicate;

use App\SmEmailSmsLog;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SmEmailSmsLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count = 5)
    {

        SmEmailSmsLog::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id,
        ]);

    }
}

<?php

namespace Database\Seeders\SystemSettings;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmHolidayTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        DB::table('sm_holidays')->insert([
            [
                'holiday_title' => 'Winter Vacation',
                'from_date' => '2019-01-22',
                'to_date' => '2019-01-28',
                'created_at' => date('Y-m-d h:i:s'),
                'church_id' => $church_id,
                'church_year_id' => $church_year_id,
            ],
            [
                'holiday_title' => 'Summer Vacation',
                'from_date' => '2019-05-02',
                'to_date' => '2019-05-08',
                'created_at' => date('Y-m-d h:i:s'),
                'church_id' => $church_id,
                'church_year_id' => $church_year_id,
            ],
            [
                'holiday_title' => 'Public Holiday',
                'from_date' => '2019-05-10',
                'to_date' => '2019-05-11',
                'created_at' => date('Y-m-d h:i:s'),
                'church_id' => $church_id,
                'church_year_id' => $church_year_id,
            ],
        ]);
    }
}

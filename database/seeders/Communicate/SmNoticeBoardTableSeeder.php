<?php

namespace Database\Seeders\Communicate;

use App\SmNoticeBoard;
use Illuminate\Database\Seeder;
use Database\Factories\SmNoticeBoardFactory;

class SmNoticeBoardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count)
    {
        SmNoticeBoard::factory()->times($count)->create([
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ]);
    }
}

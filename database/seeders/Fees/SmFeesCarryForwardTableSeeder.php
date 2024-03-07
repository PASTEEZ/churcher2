<?php

namespace Database\Seeders\Fees;

use App\SmClassSection;
use App\SmFeesCarryForward;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmFeesCarryForwardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)->where('mgender_id', $classSection->mgender_id)->where('church_id',$church_id)->where('church_year_id', $church_year_id)->get();
        foreach ($students as $student){
            $store = new SmFeesCarryForward();
            $store->member_id = $student->member_id;
            $store->balance = rand(1000,5000);
            $store->save();
        }
    }
}

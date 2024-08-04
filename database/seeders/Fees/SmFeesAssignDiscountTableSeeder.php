<?php

namespace Database\Seeders\Fees;

use App\SmClassSection;
use App\Models\StudentRecord;
use App\SmFeesAssignDiscount;
use App\SmFeesDiscount;
use Illuminate\Database\Seeder;

class SmFeesAssignDiscountTableSeeder extends Seeder
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
        $feesDisCountId= SmFeesDiscount::where('church_id',$church_id)->where('church_year_id', $church_year_id)->value('id');
        foreach ($students as $record) {
            $store = new SmFeesAssignDiscount();
            $store->fees_discount_id = $feesDisCountId;
            $store->member_id = $record->member_id;
            $store->church_id = $church_id;
            $store->church_year_id = $church_year_id;
            $store->save();
        }
    }
}

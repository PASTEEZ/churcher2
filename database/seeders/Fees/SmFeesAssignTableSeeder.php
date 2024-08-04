<?php

namespace Database\Seeders\Fees;

use App\SmFeesAssign;
use App\SmFeesMaster;
use App\SmClassSection;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmFeesAssignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)
        ->where('mgender_id', $classSection->mgender_id)
        ->where('church_id',$church_id)
        ->where('church_year_id', $church_year_id)
        ->get();
        foreach ($students as $record) {
            $val = 1 + rand() % 5;
            $fees_masters = SmFeesMaster::where('active_status', 1)
            ->where('church_id',$church_id)
            ->where('church_year_id', $church_year_id)
            ->take($val)->get();
            foreach ($fees_masters as $fees_master) {
                $store = new SmFeesAssign();
                $store->member_id = $record->member_id;
                $store->record_id = $record->id;
                $store->fees_master_id = $fees_master->id;
                $store->church_id = $church_id;
                $store->church_year_id = $church_year_id;
                $store->save();
            }
        }
    }
}

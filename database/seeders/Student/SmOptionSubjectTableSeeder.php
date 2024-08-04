<?php

namespace Database\Seeders\Student;

use App\SmClassSection;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;
use App\SmOptionalSubjectAssign;
use Illuminate\Support\Facades\DB;

class SmOptionSubjectTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=1)
    {
        $classSection = SmClassSection::where('church_id', $church_id)->where('church_year_id', $church_year_id)
        ->latest()->first();
      
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)
        ->where('mgender_id', $classSection->mgender_id)
        ->where('church_id', $church_id)
        ->where('church_year_id', $church_year_id)
        ->get();
        if ($students){
            $subjects= DB::table('sm_assign_subjects')->where('age_group_id',$classSection->age_group_id)->get();
            if(count($subjects)>0) {
                foreach ($students as $row) {
                    $s = new SmOptionalSubjectAssign();
                    $s->member_id = $row->member_id;
                    $s->session_id = $row->session_id;
                    $s->subject_id = 1;
                    $s->church_id = $church_id;
                    $s->church_year_id = $church_year_id;
                    $s->save();
                }
            }
        }
    }
}

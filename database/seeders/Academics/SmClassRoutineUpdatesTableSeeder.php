<?php

namespace Database\Seeders\Academics;

use App\SmWeekend;
use App\SmAssignSubject;
use App\SmClassRoutineUpdate;
use Illuminate\Database\Seeder;

class SmClassRoutineUpdatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=1)
    {
        $school_academic = [
            'church_id' => $church_id,
            'church_year_id' => $church_year_id,
        ];
        $classSectionSubjects=SmAssignSubject::where('church_id',$church_id)
        ->where('church_year_id',$church_year_id)
        ->get();
        $weekends = SmWeekend::where('church_id', $church_id)->get();
        foreach ($weekends as $day){
            foreach($classSectionSubjects as  $classSectionSubject){
                SmClassRoutineUpdate::factory()->times($count)->create(array_merge([
                    'day' => $day->id,
                    'age_group_id' => $classSectionSubject->age_group_id,
                    'mgender_id' => $classSectionSubject->mgender_id,
                    'subject_id' => $classSectionSubject->subject_id,
                ], $school_academic));
            }
        }

    }
}

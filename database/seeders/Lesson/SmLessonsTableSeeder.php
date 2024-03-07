<?php

namespace Database\Seeders\Lesson;

use App\SmAssignSubject;
use Illuminate\Database\Seeder;
use Modules\Lesson\Entities\SmLesson;

class SmLessonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $assignSubjects = SmAssignSubject::where('church_id', $church_id)
        ->where('church_year_id', $church_year_id)
        ->get();
        $lessons=['Chapter 01','Chapter 02','Chapter 03','Chapter 04','Chapter 05','Chapter 06','Chapter 07','Chapter 08','Chapter 09','Chapter 10','Chapter 11','Chapter 12'];
        foreach($assignSubjects as $classSec){
            foreach($lessons as $lesson){
                $smLesson=new SmLesson;
                $smLesson->lesson_title=$lesson.'.'.$classSec->id;
                $smLesson->age_group_id=$classSec->age_group_id;	
                $smLesson->subject_id=$classSec->subject_id;
                $smLesson->mgender_id=$classSec->mgender_id;
                $smLesson->church_id=$church_id;
                $smLesson->church_year_id=$church_year_id;
                $smLesson->save();

            }
        } 
    }
}

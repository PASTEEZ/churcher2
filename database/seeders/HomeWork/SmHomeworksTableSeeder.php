<?php

namespace Database\Seeders\HomeWork;

use App\SmHomework;
use App\SmAssignSubject;
use Illuminate\Database\Seeder;

class SmHomeworksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=10)
    {
        $classSectionSubjects=SmAssignSubject::where('church_id',$church_id)->where('church_year_id',$church_year_id)->get();
        foreach($classSectionSubjects as  $classSectionSubject){ 
            $s = new SmHomework();
            $s->age_group_id =  $classSectionSubject->age_group_id;
            $s->mgender_id = $classSectionSubject->mgender_id;
            $s->subject_id = $classSectionSubject->subject_id;
            $s->homework_date = date('Y-m-d');
            $s->submission_date = date('Y-m-d');
            $s->evaluation_date = date('Y-m-d');
            $s->evaluated_by = 1;
            $s->marks = rand(10, 15);
            $s->description = 'Test';
            $s->created_at = date('Y-m-d h:i:s');
            $s->church_id = $church_id;
            $s->church_year_id = $church_year_id;
            $s->save();
         }
    }
}

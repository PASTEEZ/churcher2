<?php

namespace Database\Seeders\Exam;

use App\SmAssignSubject;
use App\SmExam;
use App\SmExamSetup;
use App\SmExamType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmExamTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count = 3)
    {
        SmExamType::factory()->times($count)->create([
            'church_id' => $church_id,
            'church_year_id' => $church_year_id,
        ])->each(function($exam_type){
            $data = SmAssignSubject::withOutGlobalScopes()->where([
                'church_id' => $exam_type->church_id, 
                'church_year_id' => $exam_type->church_year_id])->get();
            foreach ($data as $row) {
                $s = new SmExamSetup();
                $s->age_group_id = $row->age_group_id;
                $s->mgender_id = $row->mgender_id;
                $s->subject_id = $row->subject_id;
                $s->exam_term_id = $exam_type->id;
                $s->church_id = $exam_type->church_id;
                $s->church_year_id = $exam_type->church_year_id;
                $s->exam_title = 'Exam';
                $s->exam_mark = 100;
                $s->created_at = date('Y-m-d h:i:s');
                $s->save();

                SmExam::create([
                    'exam_type_id' => $exam_type->id,
                    'church_id' => $exam_type->church_id,
                    'age_group_id' => $row->age_group_id,
                    'mgender_id' => $row->mgender_id,
                    'subject_id' => $row->subject_id,
                    'exam_mark' => 100,
                    'church_year_id' =>$exam_type->church_year_id,
                    'active_status' => 1,
                ]);
            }


        });
    }
}

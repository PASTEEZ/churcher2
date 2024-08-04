<?php

namespace Database\Seeders\Student;

use App\SmClassSection;
use App\SmStudentDocument;
use Faker\Factory as Faker;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmStudentDocumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=1)
    {
        $faker = Faker::create();
      
        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)
                                ->where('mgender_id', $classSection->mgender_id)
                                ->where('church_id',$church_id)
                                ->where('church_year_id', $church_year_id)
                                ->get();
        foreach($students as $student){
            $s = new SmStudentDocument();
            $s->title = $faker->sentence($nbWords =3, $variableNbWords = true);           
            $s->student_staff_id = $student->member_id;
            $s->type = 'stu';
            $s->file = '';
            $s->active_status = 1;
            $s->church_id = $church_id;
            $s->church_year_id = $church_year_id;
            $s->created_at = date('Y-m-d h:i:s');
            $s->save();
        }
    }
}

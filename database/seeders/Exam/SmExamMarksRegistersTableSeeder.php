<?php

namespace Database\Seeders\Exam;

use App\SmClass;
use App\SmStudent;
use App\SmClassSection;
use App\SmAssignSubject;
use Faker\Factory as Faker;
use App\SmExamMarksRegister;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmExamMarksRegistersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id)
    {
        $faker = Faker::create();

        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)->where('mgender_id', $classSection->mgender_id)->where('church_id',$church_id)->where('church_year_id', $church_year_id)->get();
        foreach ($students as $record) {

            $age_group_id = $record->age_group_id;
            $mgender_id = $record->mgender_id;
            $subjects = SmAssignSubject::where('church_id',$church_id)->where('church_year_id', $church_year_id)->where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->get();
            foreach ($subjects as $subject) {
                $store = new SmExamMarksRegister();
                $store->exam_id = 1;
                $store->member_id = $record->member_id;
                $store->subject_id = $subject->subject_id;
                $store->obtained_marks = rand(40, 90);
                $store->exam_date = $faker->dateTime()->format('Y-m-d');
                $store->comments = $faker->realText($maxNbChars = 50, $indexSize = 2);
                $store->created_at = date('Y-m-d h:i:s');
                $store->save();
            } //end subject
        } //end student list
    }
}

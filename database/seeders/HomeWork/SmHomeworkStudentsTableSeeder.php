<?php

namespace Database\Seeders\HomeWork;

use App\SmClass;
use App\SmHomework;
use App\SmHomeworkStudent;
use Faker\Factory as Faker;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmHomeworkStudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $faker = Faker::create();
        $class = SmClass::where('church_id', $church_id)->where('church_year_id', $church_year_id)->value('id');
        $students = StudentRecord::where('age_group_id', 1)->where('church_id', $church_id)->get();
        foreach ($students as $record) {
            $homeworks = SmHomework::where('age_group_id', $record->age_group_id)->where('church_id', 1)->get();
            foreach ($homeworks as $homework) {
                $s = new SmHomeworkStudent();
                $s->member_id = $record->member_id;
                // $s->student_record_id = $record->id;
                $s->homework_id = $homework->id;
                $s->marks = rand(5, 10);
                $s->teacher_comments = $faker->text(100);
                $s->complete_status = 'C';
                $s->created_at = date('Y-m-d h:i:s');
                $s->church_id = $church_id;
                $s->church_year_id = $church_year_id;
                $s->save();
            }
        }
    }
}

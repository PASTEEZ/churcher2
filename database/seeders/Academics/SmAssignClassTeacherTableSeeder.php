<?php

namespace Database\Seeders\Academics;

use App\SmStaff;
use App\SmClassTeacher;
use App\SmAssignClassTeacher;
use Illuminate\Database\Seeder;

class SmAssignClassTeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $church_year_id = null, $count = 5)
    {
        $teacher_id = SmStaff::where('role_id', 4)->where('church_id', $church_id)->first()->id;
        $SmAssignClassTeachers = SmAssignClassTeacher::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();
        foreach($SmAssignClassTeachers as $classTeacher) {
            $store = new SmClassTeacher();
            $store->assign_class_teacher_id = $classTeacher->id;
            $store->teacher_id = $teacher_id;
            $store->created_at = date('Y-m-d h:i:s');
            $store->church_id = $church_id;
            $store->church_year_id = $church_year_id;
            $store->save();
        }
    }
}

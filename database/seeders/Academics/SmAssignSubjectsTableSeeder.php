<?php

namespace Database\Seeders\Academics;

use App\SmStaff;
use App\SmSubject;
use App\SmClassSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmAssignSubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id)
    {
        $teacher = SmStaff::where('role_id', 4)->where('church_id', $church_id)->pluck('id')->unique();
        if($teacher){
            $data = SmClassSection::where('church_id', $church_id)->where('church_year_id', $church_year_id)->get();
        $subject_id = SmSubject::where('church_id', $church_id)->where('church_year_id', $church_year_id)->pluck('id')->unique();
        foreach ($data as $datum) {
            $age_group_id = $datum->age_group_id;
            $mgender_id = $datum->mgender_id;
            foreach ($subject_id as $subject) {
                DB::table('sm_assign_subjects')->insert([
                    [
                        'age_group_id' => $age_group_id,
                        'mgender_id' => $mgender_id,
                        'teacher_id' => $teacher[random_int(0,count($teacher)-1)] ?? $teacher[0],
                        'subject_id' => $subject,
                        'created_at' => date('Y-m-d h:i:s'),
                        'church_id'  => $church_id,
                        'church_year_id'  => $church_year_id,
                    ]
                ]);
            }
        }
        }
    }
}

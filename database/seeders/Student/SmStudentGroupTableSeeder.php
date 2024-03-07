<?php

namespace Database\Seeders\Student;

use App\SmStudentGroup;
use Illuminate\Database\Seeder;

class SmStudentGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $school_academic = [
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        SmStudentGroup::factory()->times($count)->create($school_academic);
    }
}

<?php

namespace Database\Seeders\Admin;

use App\SmStudentCertificate;
use Illuminate\Database\Seeder;

class SmStudentCertificateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=1)
    {
        $school_academic =[
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        SmStudentCertificate::factory()->times($count)->create($school_academic);

    }
}

<?php

namespace Database\Seeders\UploadContent;

use App\SmClassSection;
use App\SmTeacherUploadContent;
use Illuminate\Database\Seeder;

class SmUploadContentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $classSection = SmClassSection::where('church_id', $church_id)->where('church_year_id', $church_year_id)->first();
        SmTeacherUploadContent::factory()->times($count)->create(array_merge([
            'class' => $classSection->age_group_id,
            'section' => $classSection->mgender_id,
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ]));
    }
}

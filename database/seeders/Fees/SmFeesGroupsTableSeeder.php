<?php

namespace Database\Seeders\Fees;

use App\SmFeesType;
use App\SmFeesGroup;
use App\SmFeesMaster;
use Illuminate\Database\Seeder;

class SmFeesGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
     
        $school_academic = ['church_id'=>$church_id, 'church_year_id'=>$church_year_id];
       
        SmFeesGroup::factory()->times($count)->create($school_academic)->each(function ($feesGroup) use ($school_academic) {
            SmFeesType::factory()->times(5)->create(array_merge([
                'fees_group_id' => $feesGroup->id,
            ], $school_academic))->each(function ($feesTypes) use ($school_academic) {
                SmFeesMaster::factory()->times(1)->create(array_merge([
                    'fees_group_id' => $feesTypes->fees_group_id,
                    'fees_type_id' => $feesTypes->id,
                ], $school_academic));
            });
        });
    }
}

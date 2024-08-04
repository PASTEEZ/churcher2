<?php

namespace Database\Seeders\Admin;

use App\SmComplaint;
use App\SmSetupAdmin;
use Database\Factories\SmSetupAdminFactory;
use Illuminate\Database\Seeder;

class SmComplaintsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count = 5)
    {
        SmSetupAdmin::factory()->times($count)->create([
            'type' => 2,
            'church_id' => $church_id,
            'church_year_id' => $church_year_id
        ])->each(function ($complaint_type) use ($count){
            SmComplaint::factory()->times($count)->create([
                'church_id' => $complaint_type->church_id,
                'church_year_id' => $complaint_type->church_year_id
            ]);
        });


    }
}

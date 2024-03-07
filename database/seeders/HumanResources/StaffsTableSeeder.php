<?php

namespace Database\Seeders\HumanResources;

use App\SmRoute;
use App\SmStaff;
use App\User;
use Illuminate\Database\Seeder;

class StaffsTableSeeder  extends Seeder
{

    public function run($church_id , $count = 10){
       

        User::factory()->times($count)->create([
            'church_id' => $church_id,
        ])->each( function ($userStaff) use ($church_id) {
            SmStaff::factory()->times(1)->create([
                'user_id' => $userStaff->id,
                'email' => $userStaff->email,
                'first_name' => $userStaff->first_name,
                'last_name' => $userStaff->last_name,
                'full_name' => $userStaff->full_name,
                'church_id' => $church_id,
                'role_id' =>rand(4,9),
            ])->each(function($s){
                $s->staff_no = $s->id;
                $s->save();
            });
        });

        User::factory()->times($count)->create([
            'church_id' => $church_id,
        ])->each( function ($userStaff) use ($church_id) {
            SmStaff::factory()->times(1)->create([
                'user_id' => $userStaff->id,
                'email' => $userStaff->email,
                'first_name' => $userStaff->first_name,
                'last_name' => $userStaff->last_name,
                'full_name' => $userStaff->full_name,
                'church_id' => $church_id,
                'role_id' =>4,
            ])->each(function($s){
                $s->staff_no = $s->id;
                $s->save();
            });
        });
    }

}
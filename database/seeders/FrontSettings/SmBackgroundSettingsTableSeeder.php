<?php

namespace Database\Seeders\FrontSettings;

use App\SmBackgroundSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SmBackgroundSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $count=3)
    {
        //
        
        DB::table('sm_background_settings')->insert([
            [
               
                'title'         => 'Dashboard Background',
                'type'          => 'image',
                'image'         => 'public/backEnd/img/body-bg.jpg',
                'color'         => '',
                'is_default'    => 1,
                'church_id'     => $church_id,

            ],

            [
               
                'title'         => 'Login Background',
                'type'          => 'image',
                'image'         => 'public/backEnd/img/login-bg.jpg',
                'color'         => '',
                'is_default'    => 0,
                'church_id'     => $church_id,


            ],

        ]);
    }
}

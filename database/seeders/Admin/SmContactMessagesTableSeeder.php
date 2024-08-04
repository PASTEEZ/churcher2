<?php

namespace Database\Seeders\Admin;

use App\SmComplaint;
use App\SmContactMessage;
use App\SmSetupAdmin;
use Database\Factories\SmSetupAdminFactory;
use Illuminate\Database\Seeder;

class SmContactMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $count = 5)
    {
       SmContactMessage::factory()->times($count)->create([
           'church_id' => $church_id
       ]);


    }
}

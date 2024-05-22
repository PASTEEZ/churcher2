<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;
use App\SmVisitor;

class SmVisitorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id = 1, $count = 10)
    {
        SmVisitor::factory()->times($count)->create([
            'church_id' => $church_id,
        ]);       
    }
}

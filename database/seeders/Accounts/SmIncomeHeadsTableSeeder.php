<?php

namespace Database\Seeders\Accounts;

use App\SmAddExpense;
use App\SmAddIncome;
use App\SmExpenseHead;
use App\SmIncomeHead;
use Illuminate\Database\Seeder;

class SmIncomeHeadsTableSeeder extends Seeder
{

    public function run($church_id = 1, $count = 10){
        SmIncomeHead::factory()->times($count)->create([
            'church_id' => $church_id
        ])->each(function ($income_head){
            SmAddIncome::factory()->times(10)->create([
                'church_id' => $income_head->church_id,
                'income_head_id' => $income_head->id,
            ]);
        });
    }

}
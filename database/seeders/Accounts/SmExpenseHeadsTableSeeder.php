<?php

namespace Database\Seeders\Accounts;

use App\SmAddExpense;
use App\SmExpenseHead;
use Illuminate\Database\Seeder;

class SmExpenseHeadsTableSeeder extends Seeder
{

    public function run($church_id = 1, $count = 10){
        SmExpenseHead::factory()->times($count)->create([
            'church_id' => $church_id
        ])->each(function ($expense_head){
            SmAddExpense::factory()->times(10)->create([
                'church_id' => $expense_head->church_id,
                'expense_head_id' => $expense_head->id,
            ]);
        });
    }

}
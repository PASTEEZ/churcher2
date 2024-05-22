<?php

namespace Database\Seeders\Accounts;

use App\SmBankAccount;
use Illuminate\Database\Seeder;

class SmBankAccountsTableSeeder extends Seeder
{
    public function run($church_id = 1, $count = 10){
        SmBankAccount::factory()->times($count)->create([
            'church_id' => $church_id
        ]);
    }
}
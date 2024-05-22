<?php

namespace Database\Seeders\Fees;

use App\SmFeesType;
use App\SmFeesPayment;
use App\SmClassSection;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;

class SmFeesPaymentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        $classSection = SmClassSection::where('church_id',$church_id)->where('church_year_id', $church_year_id)->first();
        $students = StudentRecord::where('age_group_id', $classSection->age_group_id)
        ->where('mgender_id', $classSection->mgender_id)
        ->where('church_id',$church_id)
        ->where('church_year_id', $church_year_id)
        ->get();
        foreach ($students as $record) {
            $fees_types = SmFeesType::where('church_id',$church_id)
            ->where('church_year_id', $church_year_id)
            ->where('active_status', 1)
            ->get();
            foreach ($fees_types as $fees_type) {
                $store = new SmFeesPayment();
                $store->member_id = $record->member_id;
                $store->record_id = $record->id;
                $store->fees_type_id = $fees_type->id;
                $store->fees_discount_id = 1;
                $store->discount_month = date('m');
                $store->discount_amount = 100;
                $store->fine = 50;
                $store->amount = 250;
                $store->payment_mode = "C";
                $store->church_id = $church_id;
                $store->church_year_id = $church_year_id;
                $store->save();

            }
        }
    }
}

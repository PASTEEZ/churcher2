<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRecordIdToFmFeesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $feesInvoices = \Modules\Fees\Entities\FmFeesInvoice::whereNull('record_id')->get();

        foreach($feesInvoices as $invoice){
            $record = \App\Models\StudentRecord::where('church_id', $invoice->church_id)->where('church_year_id', $invoice->church_year_id)->where('age_group_id', $invoice->age_group_id)->where('member_id', $invoice->member_id)->first();
            if($record){
                $invoice->record_id = $record->id;
                $invoice->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

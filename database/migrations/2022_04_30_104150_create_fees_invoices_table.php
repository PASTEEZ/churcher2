<?php

use App\Models\FeesInvoice;
use App\SmSchool;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->nullable();
            $table->integer('start_form')->nullable();
            $table->integer('un_church_year_id')->nullable()->default(1)->unsigned();
            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $table->timestamps();
        });

        $schools = SmSchool::all();
        foreach($schools as $school){
            $store = new FeesInvoice();
            $store->prefix = 'infixEdu';
            $store->start_form = 101 + $school->id;
            $store->church_id = $school->id;
            $store->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees_invoices');
    }
}

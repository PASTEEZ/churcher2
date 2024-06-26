<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFmFeesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fm_fees_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->integer('member_id')->nullable()->unsigned();
            $table->foreign('member_id')->references('id')->on('sm_students')->onDelete('cascade');
            $table->integer('age_group_id')->nullable();
            $table->date('create_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('type')->default('fees')->nullable()->comment('fees, lms');
            $table->integer('church_id')->nullable();
            $table->integer('church_year_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fm_fees_invoices');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBankPaymentSlipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_bank_payment_slips', function (Blueprint $table) {
             $table->bigIncrements('id');

            $table->date('date');
            $table->float('amount', 10, 2)->nullable();
            $table->string('slip')->nullable();
            $table->text('note')->nullable();
            $table->integer('bank_id')->nullable();

            $table->tinyInteger('approve_status')->default(0)->comment('0 pending, 1 approve');
            $table->string('payment_mode')->comment('Bk= bank, Cq=Cheque');
            $table->text('reason')->nullable();

            $table->integer('fees_discount_id')->nullable()->unsigned();
            $table->foreign('fees_discount_id')->references('id')->on('sm_fees_discounts')->onDelete('restrict');

            $table->integer('fees_type_id')->nullable()->unsigned();
            $table->foreign('fees_type_id')->references('id')->on('sm_fees_types')->onDelete('restrict');

            $table->integer('member_id')->nullable()->unsigned();
            $table->foreign('member_id')->references('id')->on('sm_students')->onDelete('cascade');

            $table->integer('age_group_id')->unsigned();
            $table->foreign('age_group_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $table->integer('assign_id')->nullable()->unsigned();
            $table->foreign('assign_id')->references('id')->on('sm_fees_assigns')->onDelete('cascade');

            $table->integer('mgender_id')->unsigned();
            $table->foreign('mgender_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('restrict');

            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
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
        Schema::dropIfExists('sm_bank_payment_slips');
    }
}
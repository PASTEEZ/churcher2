<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmHomeworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_homeworks', function (Blueprint $table) {
            $table->increments('id');
            $table->date('homework_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->string('file', 200)->nullable();
            $table->string('marks', 200)->nullable();
            $table->string('description', 500)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('evaluated_by')->nullable()->unsigned();
            $table->foreign('evaluated_by')->references('id')->on('users')->onDelete('cascade');

            $table->integer('age_group_id')->nullable()->unsigned();
            $table->foreign('age_group_id')->references('id')->on('sm_classes')->onDelete('cascade');


            $table->integer('mgender_id')->nullable();
            // $table->foreign('mgender_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
            
            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_homeworks');
    }
}

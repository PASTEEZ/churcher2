<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmClassRoutineUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_class_routine_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('day')->nullable()->comment('1=sat,2=sun,7=fri');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();


            $table->integer('room_id')->nullable()->unsigned();
            $table->foreign('room_id')->references('id')->on('sm_class_rooms')->onDelete('cascade');

            $table->integer('teacher_id')->nullable()->unsigned();
            $table->foreign('teacher_id')->references('id')->on('sm_staffs')->onDelete('cascade');

            $table->integer('class_period_id')->nullable()->unsigned();
            $table->foreign('class_period_id')->references('id')->on('sm_class_times')->onDelete('cascade');


            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $table->integer('age_group_id')->nullable()->unsigned();
            $table->foreign('age_group_id')->references('id')->on('sm_classes')->onDelete('cascade');


            $table->integer('mgender_id')->nullable()->unsigned();
            $table->foreign('mgender_id')->references('id')->on('sm_sections')->onDelete('cascade');

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
        Schema::dropIfExists('sm_class_routine_updates');
    }
}

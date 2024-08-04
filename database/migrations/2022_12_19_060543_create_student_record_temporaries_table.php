<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentRecordTemporariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_record_temporaries', function (Blueprint $table) {
            $table->id();
            $table->integer('sm_member_id')->unsigned();
            $table->foreign('sm_member_id')->references('id')->on('sm_students')->onDelete('cascade');

            $table->bigInteger('student_record_id')->unsigned();
            $table->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade'); 

            $table->integer('user_id')->nullable();
            
            $table->integer('church_id')->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
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
        Schema::dropIfExists('student_record_temporaries');
    }
}

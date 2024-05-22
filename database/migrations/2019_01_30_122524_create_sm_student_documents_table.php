<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmStudentDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_student_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->integer('student_staff_id')->nullable();
            $table->string('type')->nullable()->comment('stu=student,stf=staff');
            $table->string('file')->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();


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
        Schema::dropIfExists('sm_student_documents');
    }
}

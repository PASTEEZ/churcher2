<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamMeritPositionsTable extends Migration
{

    public function up()
    {
        Schema::create('exam_merit_positions', function (Blueprint $table) {
            $table->id();
            $table->integer('age_group_id')->nullable();
            $table->integer('mgender_id')->nullable();
            $table->integer('exam_term_id')->nullable();
            $table->integer('total_mark')->nullable();
            $table->integer('position')->nullable();
            $table->integer('registration_no')->nullable();
            $table->float('gpa')->nullable();
            $table->float('grade')->nullable();
            $table->integer('record_id')->nullable();
            $table->integer('church_id');
            $table->integer('church_year_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_merit_positions');
    }
}

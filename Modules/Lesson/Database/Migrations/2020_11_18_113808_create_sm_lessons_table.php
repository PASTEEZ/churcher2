<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_lessons', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_title')->nullable();
            $table->integer('active_status')->default(1);

            $table->integer('age_group_id')->nullable()->unsigned();
            $table->foreign('age_group_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $table->integer('mgender_id')->nullable()->unsigned();
            $table->foreign('mgender_id')->references('id')->on('sm_sections')->onDelete('cascade');

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            
            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');  
            
            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('sm_lessons');
    }
}

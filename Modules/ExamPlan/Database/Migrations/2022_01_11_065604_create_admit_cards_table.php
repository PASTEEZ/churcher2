<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdmitCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admit_cards', function (Blueprint $table) {
            $table->id();
            $table->integer('student_record_id');
            $table->integer('exam_type_id');
            $table->integer('created_by');
            $table->integer('school_id')->nullable()->default(1)->unsigned();
            $table->integer('academic_id')->nullable()->default(1)->unsigned();
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
        Schema::dropIfExists('admit_cards');
    }
}

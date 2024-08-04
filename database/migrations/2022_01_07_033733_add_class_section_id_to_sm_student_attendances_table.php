<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassSectionIdToSmStudentAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sm_student_attendances', function (Blueprint $table) {
            
            $table->bigInteger('student_record_id')->nullable()->unsigned();
            $table->foreign('student_record_id')->references('id')->on('student_records')->onDelete('cascade');

            $table->integer('age_group_id')->nullable()->unsigned();
            $table->foreign('age_group_id')->references('id')->on('sm_classes')->onDelete('cascade');

            $table->integer('mgender_id')->nullable()->unsigned();
            $table->foreign('mgender_id')->references('id')->on('sm_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sm_student_attendances', function (Blueprint $table) {
            $table->dropColumn('student_record_id');
            $table->dropColumn('age_group_id');
            $table->dropColumn('mgender_id');
        });
    }
}

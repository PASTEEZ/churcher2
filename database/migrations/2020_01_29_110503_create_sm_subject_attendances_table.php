<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SmSubjectAttendance;
class CreateSmSubjectAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_subject_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attendance_type', 10)->nullable()->comment('Present: P Late: L Absent: A Holiday: H Half Day: F');
            $table->string('notes', 500)->nullable();
            $table->date('attendance_date')->nullable();
            $table->timestamps();

            $table->integer('subject_id')->nullable()->unsigned();
            $table->foreign('subject_id')->references('id')->on('sm_subjects')->onDelete('cascade');

            $table->integer('member_id')->nullable()->unsigned();
            $table->foreign('member_id')->references('id')->on('sm_students')->onDelete('cascade');

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
        Schema::dropIfExists('sm_subject_attendances');
    }
}

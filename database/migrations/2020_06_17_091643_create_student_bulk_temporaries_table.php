<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentBulkTemporariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_bulk_temporaries', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->nullable();
            $table->string('member_id_no')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('gender')->nullable();

            $table->string('home_town')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('region')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();

            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_occupation')->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address')->nullable();

            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('day_born')->nullable();
            $table->string('employer_name')->nullable();

            $table->string('national_identification_no')->nullable();
            $table->string('local_identification_no')->nullable();
            $table->string('previous_school_details')->nullable();
            $table->string('note')->nullable();
            
            $table->string('user_id')->nullable();

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
        Schema::dropIfExists('student_bulk_temporaries');
    }
}
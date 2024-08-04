<?php

use App\Scopes\AcademicSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmStudentTakeOnlineExam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleCourseDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Promoted data
        $promotes = \App\SmStudentPromotion::all();
        foreach ($promotes as $promote) {
            $class = \App\SmClass::withOutGlobalScope(StatusAcademicSchoolScope::class)->where(['id' => $promote->age_group_id, 'church_id' => $promote->church_id])->first();
            $studentRecords = \App\Models\StudentRecord::firstOrCreate([
                'member_id' => $promote->member_id,
                'church_id' => $promote->church_id,
                'age_group_id' => $promote->previous_age_group_id,
                'mgender_id' => $promote->previous_mgender_id,
                'session_id' => $promote->previous_session_id,
                'church_year_id' => $promote->previous_session_id,
                'roll_no' => $promote->previous_roll_number,
                'is_promote' => 1,
            ]);
        }
        // Student data migration

        $students = \App\SmStudent::all();

        foreach ($students as $student) {
            if ($student->age_group_id && $student->mgender_id && $student->session_id && $student->church_year_id) {
                \App\Models\StudentRecord::firstOrCreate([
                    'member_id' => $student->id,
                    'church_id' => $student->church_id,
                    'age_group_id' => $student->age_group_id,
                    'mgender_id' => $student->mgender_id,
                    'session_id' => $student->session_id,
                    'church_year_id' => $student->church_year_id,
                    'roll_no' => $student->roll_no,
                    'is_default' => 1,
                ]);
            }

            $student->age_group_id = null;
            $student->mgender_id = null;
            $student->session_id = null;
            $student->church_year_id = null;
            $student->roll_no = null;
            $student->save();

            // Fees data migration

        }


    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use App\Scopes\AcademicSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmStudentTakeOnlineExam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleCourseMigrationFixingMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fees = \App\SmFeesAssign::withOutGlobalScope(StatusAcademicSchoolScope::class)->get();

        foreach ($fees as $fee) {
            $record = \App\Models\StudentRecord::where(['member_id' => $fee->member_id, 'church_id' => $fee->church_id, 'church_year_id' => $fee->church_year_id])->first();
            $fee->record_id = optional($record)->id;
            $fee->save();
        }

        $fees = \App\SmFeesPayment::all();

        foreach ($fees as $fee) {
            $record = \App\Models\StudentRecord::where(['member_id' => $fee->member_id, 'church_id' => $fee->church_id, 'church_year_id' => $fee->church_year_id])->first();
            $fee->record_id = optional($record)->id;
            $fee->save();
        }


        $feeDiscounts = \App\SmFeesAssignDiscount::withOutGlobalScope(StatusAcademicSchoolScope::class)->get();

        foreach ($feeDiscounts as $feefeeDiscount) {
            $record = \App\Models\StudentRecord::where(['member_id' => $feefeeDiscount->member_id, 'church_id' => $feefeeDiscount->church_id, 'church_year_id' => $feefeeDiscount->church_year_id])->first();
            $feefeeDiscount->record_id = optional($record)->id;
            $feefeeDiscount->save();
        }


        $onlineExams = SmStudentTakeOnlineExam::all();

        foreach ($onlineExams as $onlineExam) {
            $record = \App\Models\StudentRecord::where(['member_id' => $onlineExam->member_id, 'church_id' => $onlineExam->church_id, 'church_year_id' => $onlineExam->church_year_id])->first();
            $onlineExam->record_id = optional($record)->id;
            $onlineExam->save();
        }

        // Attendance data migration

        $attendances = \App\SmStudentAttendance::withOutGlobalScope(AcademicSchoolScope::class)->get();

        foreach ($attendances as $attendance) {
            $record = \App\Models\StudentRecord::where(['member_id' => $attendance->member_id, 'church_id' => $attendance->church_id, 'church_year_id' => $attendance->church_year_id, 'age_group_id' => $attendance->age_group_id, 'mgender_id' => $attendance->mgender_id])->first();
            $attendance->student_record_id = optional($record)->id;
            $attendance->save();
        }

        $subjectAttendances = \App\SmSubjectAttendance::all();

        foreach ($subjectAttendances as $attendance) {
            $record = \App\Models\StudentRecord::where(['member_id' => $attendance->member_id, 'church_id' => $attendance->church_id, 'church_year_id' => $attendance->church_year_id, 'age_group_id' => $attendance->age_group_id, 'mgender_id' => $attendance->mgender_id])->first();
            $attendance->student_record_id = optional($record)->id;
            $attendance->save();
        }


        $examAttendances = \App\SmExamAttendanceChild::all();

        foreach ($examAttendances as $examAttendance) {
            $record = \App\Models\StudentRecord::where(['member_id' => $examAttendance->member_id, 'church_id' => $examAttendance->church_id, 'church_year_id' => $examAttendance->church_year_id, 'age_group_id' => $examAttendance->age_group_id, 'mgender_id' => $examAttendance->mgender_id])->first();
            $examAttendance->student_record_id = optional($record)->id;
            $examAttendance->save();
        }

        $datas = \App\SmResultStore::all();

        foreach ($datas as $data) {
            $record = \App\Models\StudentRecord::where(['member_id' => $data->member_id, 'church_id' => $data->church_id, 'church_year_id' => $data->church_year_id, 'age_group_id' => $data->age_group_id, 'mgender_id' => $data->mgender_id])->first();
            $data->student_record_id = optional($record)->id;
            $data->save();
        }


        $datas = \App\SmMarkStore::withOutGlobalScope(AcademicSchoolScope::class)->get();

        foreach ($datas as $data) {
            $record = \App\Models\StudentRecord::where(['member_id' => $data->member_id, 'church_id' => $data->church_id, 'church_year_id' => $data->church_year_id, 'age_group_id' => $data->age_group_id, 'mgender_id' => $data->mgender_id])->first();
            $data->student_record_id = optional($record)->id;
            $data->save();
        }

        $schools = \App\SmSchool::all();
        foreach($schools as $school){
            $setting = \App\SmGeneralSettings::where('church_id', $school->id)->first();

            if($setting && !$setting->church_year_id){
                $church_year = \App\SmAcademicYear::where('church_id', $school->id)->first();
                $setting->church_year_id = $church_year ? $church_year->id : null;
                $setting->save();
            }
        }

        \App\Models\SmStudentRegistrationField::where('field_name', 'admission_number')->update(['is_required' => 1]);
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

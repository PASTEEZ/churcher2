<?php

namespace App;

use App\Models\JyMemberRecord;
use App\Models\StudentRecord;
use App\Scopes\SchoolScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\University\Entities\UnSubject;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\OnlineExam\Entities\InfixPdfExam;
use Modules\OnlineExam\Entities\InfixOnlineExam;
use Modules\University\Entities\UnSubjectComplete;
use Modules\FeesCollection\Entities\InfixFeesMaster;
use Modules\FeesCollection\Entities\InfixFeesPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FeesCollection\Entities\InfixAssignDiscount;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;

class SmJymember extends Model
{
    use HasFactory;
    protected $fillable = [];

    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope(new SchoolScope);

    }
    public function parents()
    {
        return $this->belongsTo('App\SmParent', 'parent_id', 'id')->withDefault()->with('parent_user');
    }

    public function getOptionalSubjectSetupAttribute()
    {
        return SmClassOptionalSubject::where('age_group_id', $this->age_group_id)->first();
    }
    public function optionalSubject()
    {
        return $this->belongsTo('App\SmOptionalSubjectAssign', 'member_id', 'id');
    }
    public function drivers()
    {
        return $this->belongsTo('App\SmStaff', 'driver_id', 'id');
    }

    public function roles()
    {
        return $this->belongsTo('App\Role', 'role_id', 'id');
    }

    public function feesPayment()
    {
        return $this->hasMany(SmFeesPayment::class, 'member_id');
    }

    public function gender()
    {
        return $this->belongsTo('App\SmBaseSetup', 'gender_id', 'id')->withDefault()->withDefault();
    }

    public function school()
    {
        return $this->belongsTo('App\SmSchool', 'church_id', 'id');
    }

    public function religion()
    {
        return $this->belongsTo('App\SmBaseSetup', 'religion_id', 'id')->withDefault()->withDefault();
    }

    public function bloodGroup()
    {
        return $this->belongsTo('App\SmBaseSetup', 'bloodgroup_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo('App\SmStudentCategory', 'student_category_id', 'id')->withDefault()->withDefault();
    }

    public function group()
    {
        return $this->belongsTo('App\SmStudentGroup', 'student_group_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo('App\SmSession', 'session_id', 'id');
    }

    public function academicYear()
    {
        return $this->belongsTo('App\SmAcademicYear', 'church_year_id', 'id');
    }

    //student class name
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id')->withoutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function route()
    {
        return $this->belongsTo('App\SmRoute', 'route_list_id', 'id');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\SmVehicle', 'vechile_id', $this->vehicle_id);
    }

    public function dormitory()
    {
        return $this->belongsTo('App\SmDormitoryList', 'dormitory_id', 'id');
    }

    public function sections()
    {
        return $this->hasManyThrough('App\SmSection', 'App\SmClassSection', 'age_group_id', 'id', 'age_group_id', 'mgender_id');
    }

    public function rooms()
    {
        return $this->hasMany('App\SmRoomList', 'dormitory_id', 'dormitory_id');
    }

    public function room()
    {
        return $this->belongsTo('App\SmRoomList', 'room_id', 'id');
    }

    public function attendances()
    {
        return $this->hasMany(SmStudentAttendance::class, 'member_id');
    }

    public function forwardBalance()
    {
        return $this->belongsTo('App\SmFeesCarryForward', 'id', 'member_id');
    }

    public function meritList()
    {
        return $this->belongsTo('App\SmTemporaryMeritlist', 'id', 'member_id');
    }

    public function feesAssign()
    {
        return $this->hasMany('App\SmFeesAssign', 'member_id', 'id');
    }

    public function feesAssignDiscount()
    {
        return $this->hasMany('App\SmFeesAssignDiscount', 'member_id', 'id');
    }

    public function studentDocument()
    {
        return $this->hasMany('App\SmStudentDocument', 'student_staff_id', 'id');
    }

    public function studentTimeline()
    {
        return $this->hasMany('App\SmStudentTimeline', 'staff_member_id', 'id');
    }

    public function studentLeave()
    {
        return $this->hasMany('App\SmLeaveRequest', 'staff_id', $this->user_id)->where('role_id', 2);
    }

    public function getClass()
    {
        return $this->belongsTo(CheckClass::class, 'age_group_id');
    }

    public function getAttendanceType($month)
    {
        return $this->attendances()->whereMonth('attendance_date', $month)->get();
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    public function assignDiscount()
    {
        return $this->hasMany(InfixAssignDiscount::class, 'member_id');
    }

    public function feesMasters()
    {
        return $this->hasMany(InfixFeesMaster::class, 'age_group_id', 'age_group_id');
    }

    public function markStores()
    {
        return $this->hasMany(SmMarkStore::class, 'member_id')->where('age_group_id', $this->age_group_id)
            ->where('mgender_id', $this->mgender_id);
    }

    public function assignSubjects()
    {
        return $this->hasMany(SmAssignSubject::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)->where('active_status', 1);
    }

    public function studentOnlineExams()
    {

        if (moduleStatusCheck('OnlineExam') == true) {
            return $this->hasMany(InfixOnlineExam::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)
                ->where('active_status', 1)->where('status', 1)->where('church_id', Auth::user()->church_id);
        } else {
            return $this->hasMany(SmOnlineExam::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)
                ->where('active_status', 1)->where('status', 1)->where('church_id', Auth::user()->church_id);
        }

    }
    public function studentPdfExams()
    {

        return $this->hasMany(InfixPdfExam::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)
            ->where('active_status', 1)->where('status', 1)->where('church_id', Auth::user()->church_id);

    }

    public function scheduleBySubjects()
    {
        return $this->hasMany(SmExamSchedule::class, 'age_group_id', 'age_group_id')
            ->where('mgender_id', $this->mgender_id);
    }

    public function assignSubject()
    {
        return $this->hasMany(SmAssignSubject::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)->distinct('teacher_id');
    }

    public function bookIssue()
    {
        return $this->hasMany(SmBookIssue::class, 'member_id', 'user_id')->where('issue_status', 'I');
    }

    public function examSchedule()
    {
        return $this->hasMany(SmExamSchedule::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id);
    }

    public function homework()
    {
        return $this->hasMany(SmHomework::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id)
            ->where('evaluation_date', '=', null)->where('submission_date', '>', date('Y-m-d'));
    }

    public function studentAttendances()
    {
        return $this->hasMany(SmStudentAttendance::class, 'member_id')->where('attendance_date', 'like', date('Y') . '-' . date('m') . '%')
            ->where('attendance_type', 'P');
    }

    public function studentOnlineExam()
    {
        if (moduleStatusCheck('OnlineExam') == true) {
            return $this->hasMany(InfixStudentTakeOnlineExam::class, 'member_id');
        } else {
            return $this->hasMany(SmStudentTakeOnlineExam::class, 'member_id');
        }

    }

    public function examsSchedule()
    {
        return $this->hasMany(SmExamSchedule::class, 'age_group_id', 'age_group_id')->where('mgender_id', $this->mgender_id);
    }
    public function homeworkContents()
    {
        return $this->hasMany(SmUploadHomeworkContent::class, 'member_id');
    }

    public function bankSlips()
    {
        return $this->hasMany(SmBankPaymentSlip::class, 'member_id');
    }

    public static function totalFees($feesAssigns)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feesAssign) {
                $master = SmFeesMaster::select('fees_group_id', 'amount', 'date')->where('id', $feesAssign->fees_master_id)->first();

                $due_date = strtotime($master->date);
                $now = strtotime(date('Y-m-d'));
                if ($due_date > $now) {
                    continue;
                }
                $amount += $master->amount;
            }
            return $amount;
        } catch (\Exception $e) {
            $data = [];
            $data[0] = $e->getMessage();
            return $data;
        }
    }

    public function getTotalAmount()
    {
        $amount = 0;
        foreach ($this->feesAssign as $feesAssign) {
            $amount += $feesAssign->feesGroupMaster->amount;
        }
        return $amount;
    }

    public function getTotalDiscount($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feesAssign) {
            $amount += SmFeesAssign::where('fees_type_id', $feesAssign->feesGroupMaster->fees_type_id)->where('member_id', $id)->sum('discount_amount');
        }
        return $amount;
    }

    public function getTotalFine($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feesAssign) {
            $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $feesAssign->feesGroupMaster->fees_type_id)->where('member_id', $id)->sum('fine');
        }
        return $amount;
    }

    public function getTotalDeposit($id)
    {
        $amount = 0;
        foreach ($this->feesAssign as $feesAssign) {
            $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $feesAssign->feesGroupMaster->fees_type_id)->where('member_id', $id)->sum('amount');
        }
        return $amount;
    }

    public static function totalDeposit($feesAssigns, $member_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feesAssign) {
                $fees_type = SmFeesMaster::select('fees_type_id')->where('id', $feesAssign->fees_master_id)->first();
                $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $fees_type->fees_type_id)->where('member_id', $member_id)->sum('amount');
            }
            return $amount;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function totalDiscount($feesAssigns, $member_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feesAssign) {
                $amount = SmFeesAssign::where('member_id', $member_id)->sum('applied_discount');
            }
            return $amount;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function totalFine($feesAssigns, $member_id)
    {

        try {
            $amount = 0;
            foreach ($feesAssigns as $feesAssign) {
                $fees_type = SmFeesMaster::select('fees_type_id')->where('id', $feesAssign->fees_master_id)->first();
                $amount += SmFeesPayment::where('active_status', 1)->where('fees_type_id', $fees_type->fees_type_id)->where('member_id', $member_id)->sum('fine');
            }
            return $amount;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public static function marks($exam_id, $s_id)
    {

        try {
            $marks_register = SmMarksRegister::where('exam_id', $exam_id)->where('member_id', $s_id)->first();
            $marks_register_clilds = [];
            if ($marks_register != "") {
                $marks_register_clilds = SmMarksRegisterChild::where('marks_register_id', $marks_register->id)->where('active_status', 1)->get();
            }
            return $marks_register_clilds;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function fullMarks($exam_id, $sb_id)
    {
        try {
            return SmExamScheduleSubject::where('exam_schedule_id', $exam_id)->where('subject_id', $sb_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function fullMarksBySubject($exam_id, $sb_id)
    {
        try {
            return SmExamSetup::where('exam_term_id', $exam_id)->where('subject_id', $sb_id)->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_fullMarksBySubject($exam_id, $sb_id, $request)
    {
        try {
            $SmExamSetup = SmExamSetup::query();
            return universityFilter($SmExamSetup, $request)
                    ->where('exam_term_id', $exam_id)
                    ->where('un_subject_id', $sb_id)
                    ->first();
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function un_scheduleBySubject($exam_id, $sb_id, $request)
    {
        try {
            $SmExamSchedule = SmExamSchedule::query();
            $schedule = universityFilter($SmExamSchedule, $request)
                ->where('exam_term_id', $exam_id)
                ->where('un_subject_id', $sb_id)
                ->first();
            return $schedule;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function scheduleBySubject($exam_id, $sb_id, $record)
    {
        try {
            $schedule = SmExamSchedule::where('exam_term_id', $exam_id)
                ->where('subject_id', $sb_id)
                ->where('age_group_id', $record->age_group_id)
                ->where('mgender_id', $record->mgender_id)
                ->first();
            return $schedule;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function promotion()
    {
        return $this->hasMany('App\SmStudentPromotion', 'member_id', 'id');
    }

    public function feesPayments()
    {
        return $this->hasMany(InfixFeesPayment::class, 'member_id');
    }

    public function getClassesAttribute()
    {
        $classes = '';
        if (count($this->promotion) > 0) {
            $maxClass = $this->promotion->max('current_age_group_id');
            $minClass = $this->promotion->min('previous_age_group_id');
            $classes = $minClass . ' - ' . $maxClass;
        } else {
            $classes = $this->class->age_group_name . ' - ' . $this->class->age_group_name;
        }

        return $classes;
    }

    public function getSessionsAttribute()
    {
        $sessions = '';
        if (count($this->promotion) > 0) {
            $maxSession = $this->promotion->max('current_session_id');
            $minSession = $this->promotion->min('previous_session_id');
            $maxYear = SmAcademicYear::find($maxSession)->year ?? "";
            $minYear = SmAcademicYear::find($minSession)->year ?? "";
            $sessions = $minYear . ' - ' . $maxYear;
        } else {
            @$sessions = $this->academicYear->year . ' - ' . $this->academicYear->year;
        }

        return $sessions;
    }

    public static function classPromote($class)
    {
        try {
            $class = SmClass::where('id', $class)->first();
            return $class->age_group_name;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function sessionPromote($session)
    {
        try {
            $session = SmSession::where('id', $session)->first();
            return $session->session;
        } catch (\Exception $e) {
            $data = [];
            return $data;
        }
    }

    public static function getExamResult($exam_id, $record)
    {
        $eligible_subjects = SmAssignSubject::where('age_group_id', $record->age_group_id)
                            ->where('mgender_id', $record->mgender_id)
                            ->groupBy(['mgender_id', 'subject_id'])
                            ->where('church_year_id', getAcademicId())
                            ->where('church_id', Auth::user()->church_id)
                            ->get();

        foreach ($eligible_subjects as $subject) {
            $getMark = SmResultStore::where([
                ['exam_type_id', $exam_id],   
                ['member_id', $record->member_id],
                ['student_record_id', $record->id],
                ['subject_id', $subject->subject_id],
            ])->first();

            if ($getMark == "") {
                continue;
            }

            $result = SmResultStore::where([
                ['exam_type_id', $exam_id],
                ['member_id', $record->member_id],
                ['student_record_id', $record->id],
            ])->get();

            return $result;
        }
        return [];
    }

    public static function un_getExamResult($exam_id, $record, $request)
    {
        $SmExamSetup = SmExamSetup::query();
        $eligible_subjects = universityFilter($SmExamSetup, $request)
                            ->where('exam_term_id', $exam_id)
                          
                            ->get();
       
        foreach ($eligible_subjects as $subject) {
            $SmResultStore = SmResultStore::query();
            $getMark = universityFilter($SmResultStore, $request)
                                ->where([
                                    ['exam_type_id', $exam_id],   
                                    ['member_id', $record->member_id],
                                    ['student_record_id', $record->id],
                                    ['un_subject_id', $subject->un_subject_id],
                                ])->first();
                                    
            if ($getMark == "") {
                return false;
            }

            $SmResultStore = SmResultStore::query();
            $result = universityFilter($SmResultStore, $request)
                    ->where([
                        ['exam_type_id', $exam_id],
                        ['member_id', $record->member_id],
                        ['student_record_id', $record->id],
                    ])->get();

            return $result;
        }
    }

    public function examAttendances()
    {
        return $this->hasMany(SmExamAttendanceChild::class, 'member_id');
    }

    public function homeworks()
    {
        return $this->hasMany(SmHomeworkStudent::class, 'member_id');
    }

    public function onlineExams()
    {
        return $this->hasMany(SmStudentTakeOnlineExam::class, 'member_id');
    }

    public function subjectAssign()
    {
        return $this->hasOne(SmOptionalSubjectAssign::class, 'member_id')->where('church_year_id', getAcademicId());
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('church_id', Auth::user()->church_id);
    }

    public function DateWiseAttendances()
    {
        if (moduleStatusCheck('Univeristy')) {
            $request = request();
            return $this->hasOne(SmStudentAttendance::class, 'member_id')
            ->when($request->un_session_id, function ($q) use ($request) {
                $q->where('un_session_id', $request->un_session_id);
            })
            ->when($request->un_faculty_id, function ($q) use ($request) {
                $q->where('un_faculty_id', $request->un_faculty_id);
            })
            ->when($request->un_department_id, function ($q) use ($request) {
                $q->where('un_department_id', $request->un_department_id);
            })
            ->when($request->un_church_year_id, function ($q) use ($request) {
                $q->where('un_church_year_id', $request->un_church_year_id);
            })
            ->when($request->un_semester_id, function ($q) use ($request) {
                $q->where('un_semester_id', $request->un_semester_id);
            })
            ->when($request->un_semester_label_id, function ($q) use ($request) {
                $q->where('un_semester_label_id', $request->un_semester_label_id);
            })->where('church_id', auth()->user()->church_id)
            ->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        } else {
            return $this->hasOne(SmStudentAttendance::class, 'member_id')
            ->where('age_group_id', request()->class)
            ->where('mgender_id', request()->section)
            ->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        }
    }
    public function DateSubjectWiseAttendances()
    {
        if (moduleStatusCheck('University')) {
            return $this->hasOne(SmSubjectAttendance::class, 'member_id')->where('un_semester_label_id', request()->un_semester_label_id)->where('un_subject_id', request()->un_subject_id)->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        } else {
            return $this->hasOne(SmSubjectAttendance::class, 'member_id')->where('age_group_id', request()->class)->where('mgender_id', request()->section)->where('subject_id', request()->subject)->where('attendance_date', date('Y-m-d', strtotime(request()->attendance_date)));
        }
    }
    public function lead()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo('Modules\Lead\Entities\Lead', 'lead_id', 'id')->withDefault();
        }
    }
    public function leadCity()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo('Modules\Lead\Entities\LeadCity', 'lead_city_id', 'id')->withDefault();
        }  
    }
    public function source()
    {
        if (moduleStatusCheck('Lead') == true) {
            return $this->belongsTo('Modules\Lead\Entities\Source', 'source_id', 'id')->withDefault();
        }  
    }
    public function studentAllRecords()
    {
        return $this->hasMany(JyMemberRecord::class, 'member_id', 'id')->where('is_promote', 0)->orderBy('id', 'DESC');
    }
    public function studentRecords()
    {
        return $this->hasMany(JyMemberRecord::class, 'member_id', 'id')->where('is_promote', 0)->where('active_status', 1)->orderBy('id', 'DESC');
    }

    public function orderByStudentRecords()
    {
        return $this->hasMany(JyMemberRecord::class, 'member_id', 'id')->where('is_promote', 0)->where('active_status', 1)->orderBy('id', 'DESC');
    }
    public function getClassRecord()
    {
        return $this->hasMany(JyMemberRecord::class, 'member_id', 'id')->where('is_promote', 0)->groupBy('age_group_id');
    }
    public function studentRecord()
    {
        return $this->hasOne(JyMemberRecord::class, 'member_id')->where('is_promote', 0)
        ->when(moduleStatusCheck('University')==false, function ($q) {
            $q->where('church_year_id', getAcademicId());
        })->where('church_id', Auth::user()->church_id);
    }
    public function defaultClass()
    {        
        return $this->hasOne(JyMemberRecord::class, 'member_id')->where('is_promote', 0)->latest()->where('is_default', 1)
        ->when(moduleStatusCheck('University'), function ($query) {
            $query->where('un_church_year_id', getAcademicId());
        }, function ($query) {
            $query->where('church_year_id', getAcademicId());
        })->where('church_id', Auth::user()->church_id);
    }
    public function recordClass()
    {
        return $this->hasOne(JyMemberRecord::class, 'member_id')->where('is_promote', 0)->where('age_group_id', request()->class)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id);
    }
    
    public function recordSection()
    {
        return $this->hasOne(JyMemberRecord::class, 'member_id')->where('is_promote', 0)->where('age_group_id', request()->class)->where('mgender_id', request()->section)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id);
    }
    public function recordClasses()
    {
        return $this->hasMany(JyMemberRecord::class, 'member_id')->where('is_promote', 0)->where('age_group_id', request()->class)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id);
    }
    public function recordStudentRoll()
    {
        return $this->hasOne(JyMemberRecord::class, 'member_id')->where('is_promote', 0)->where('age_group_id', request()->current_class)->where('mgender_id', request()->current_section)->where('church_year_id', request()->current_session)->where('church_id', Auth::user()->church_id);
    }

    public function completeSubjects()
    {
        return $this->hasMany(UnSubjectComplete::class, 'member_id', 'id')->where('is_pass', 'pass');
    }

    public function lastRecord()
    {
        return $this->hasOne(JyMemberRecord::class, 'member_id', 'id')->where('is_promote', 0)->latest();
    }
    
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    public function getRollNoAttribute($value){
        if(generalSetting()->multiple_roll){
            $this->load('recordClass');
            if($this->recordClass){
                return $this->recordClass->roll_no;
            }

            $this->load('studentRecords');
            if($this->studentRecords->count()){
                return $this->studentRecords()->latest()->first()->roll_no;
            }

        }

        return $value;
    }
}

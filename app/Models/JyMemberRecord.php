<?php

namespace App\Models;

use App\SmExam;
use App\SmClass;
use App\SmExamType;
use App\SmHomework;
use App\SmFeesAssign;
use App\SmOnlineExam;
use App\SmResultStore;
use App\SmAssignSubject;
use App\SmStudentAttendance;
use App\SmFeesAssignDiscount;
use App\SmClassOptionalSubject;
use App\SmTeacherUploadContent;
use App\SmStudentTakeOnlineExam;
use Modules\Lms\Entities\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Modules\Zoom\Entities\VirtualClass;
use Modules\ExamPlan\Entities\AdmitCard;
use Modules\Fees\Entities\FmFeesInvoice;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\BBB\Entities\BbbVirtualClass;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\University\Entities\UnSubject;
use Modules\Gmeet\Entities\GmeetVirtualClass;
use Modules\Jitsi\Entities\JitsiVirtualClass;
use Modules\OnlineExam\Entities\InfixPdfExam;
use Modules\OnlineExam\Entities\InfixOnlineExam;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSubjectComplete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use JoisarJignesh\Bigbluebutton\Facades\Bigbluebutton;
use Modules\University\Entities\UnSubjectPreRequisite;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;

class JyMemberRecord extends Model
{
    use HasFactory;
 
    protected $table = 'sm_jymembers';
	public $timestamps = true;
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'admission_no',
        'first_name',
      

	];
 
}

<?php

namespace Modules\Lesson\Http\Controllers;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmSubject;
use App\SmAssignSubject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\Lesson\Entities\SmLesson;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\Lesson\Entities\SmLessonTopic;
use Modules\Lesson\Entities\SmLessonTopicDetail;
use Modules\University\Entities\UnSubject;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmLessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }
    public function index()
    {
        try {
            $data = $this->loadLesson();
            return view('lesson::lesson.add_new_lesson', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function storeLesson(Request $request)
    {
        if (moduleStatusCheck('University')) {
            $request->validate(
                [
                    'un_session_id' => 'required',
                    'un_faculty_id' => 'sometimes|nullable',
                    'un_department_id' => 'required',
                    'un_church_year_id' => 'required',
                    'un_semester_id' => 'required',
                    'un_semester_label_id' => 'required',
                    'un_subject_id' => 'required',
                    'un_mgender_id' => 'sometimes|nullable',
                ],
            );
        } else {
            $request->validate(
                [
                    'class' => 'required',
                    'subject' => 'required',
                ],
            );
        }

        DB::beginTransaction();
        try {
            $sections = SmAssignSubject::where('age_group_id', $request->class)
                ->where('subject_id', $request->subject)
                ->get();
            if (moduleStatusCheck('University')) {
                if ($request->un_mgender_id) {
                    $sections = UnSubject::where('un_department_id', $request->un_department_id)
                    ->where('church_id', auth()->user()->church_id)
                    ->get();
                } else {
                    $sections = $request->un_mgender_id;
                }
            }
            foreach ($sections as $section) {
                foreach ($request->lesson as $lesson) {
                    $smLesson = new SmLesson;
                    $smLesson->lesson_title = $lesson;
                    $smLesson->age_group_id = $request->class;
                    $smLesson->subject_id = $request->subject;
                    $smLesson->mgender_id = $section->mgender_id;
                    $smLesson->church_id = auth()->user()->church_id;
                    $smLesson->user_id = auth()->user()->id;
                    if (moduleStatusCheck('University')) {
                        $common = App::make(UnCommonRepositoryInterface::class);
                        $common->storeUniversityData($smLesson, $request);
                    }else{
                        $smLesson->church_year_id = getAcademicId();
                    }
                    $smLesson->save();
                }
            }
            DB::commit();

            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editLesson($age_group_id, $mgender_id, $subject_id)
    {
        try {
            $data = $this->loadLesson();
            $data['lesson'] = SmLesson::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id], ['subject_id', $subject_id]])->first();
            $data['lesson_detail'] = SmLesson::where([['age_group_id', $age_group_id], ['mgender_id', $mgender_id], ['subject_id', $subject_id]])->get();
            return view('lesson::lesson.edit_lesson', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function editLessonForUniVersity($session_id, $faculty_id = null, $department_id, $church_year_id, $semester_id, $semester_label_id, $subject_id)
    {
        try {
            $data = $this->loadLesson();
            $lesson = SmLesson::when($session_id, function ($query) use ($session_id) {
                $query->where('un_session_id', $session_id);
            })->when($faculty_id !=0, function ($query) use ($faculty_id) {
                $query->where('un_faculty_id', $faculty_id);
            })->when($department_id, function ($query) use ($department_id) {
                $query->where('un_department_id', $department_id);
            })->when($church_year_id, function ($query) use ($church_year_id) {
                $query->where('un_church_year_id', $church_year_id);
            })->when($semester_id, function ($query) use ($semester_id) {
                $query->where('un_semester_id', $semester_id);
            })->when($semester_label_id, function ($query) use ($semester_label_id) {
                $query->where('un_semester_label_id', $semester_label_id);
            })->when($subject_id !=0, function ($query) use ($subject_id) {
                $query->where('un_subject_id', $subject_id);
            });
            $data['lesson_detail'] = $lesson->get();
            $data['lesson'] = $lesson->first();
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($data['lesson']);
            return view('lesson::lesson.edit_lesson', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function updateLesson(Request $request)
    {
        try {
            $length = count($request->lesson);
            for ($i = 0; $i < $length; $i++) {
                $lessonDetail = SmLesson::find($request->lesson_detail_id[$i]);
                $lesson_title = $request->lesson[$i];
                $lessonDetail->lesson_title = $lesson_title;
                $lessonDetail->church_id = Auth::user()->church_id;
                $lessonDetail->church_year_id = getAcademicId();
                $lessonDetail->user_id = Auth::user()->id;
                $lessonDetail->save();
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function deleteLesson($id)
    {
        $lesson = SmLesson::find($id);
        $lesson_detail = SmLesson::where([['age_group_id', $lesson->age_group_id], ['mgender_id', $lesson->mgender_id], ['subject_id', $lesson->subject_id]])->get();
        foreach ($lesson_detail as $lesson_data) {
            SmLesson::destroy($lesson_data->id);
        }
        $SmLessonTopic = SmLessonTopic::where('lesson_id', $id)->get();
        if ($SmLessonTopic) {
            foreach ($SmLessonTopic as $t_data) {
                SmLessonTopic::destroy($t_data->id);
            }
        }
        $SmLessonTopicDetail = SmLessonTopicDetail::where('lesson_id', $id)->get();
        if ($SmLessonTopicDetail) {
            foreach ($SmLessonTopicDetail as $td_data) {
                SmLessonTopicDetail::destroy($td_data->id);
            }
        }
        $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
        if ($LessonPlanner) {
            foreach ($LessonPlanner as $lp_data) {
                LessonPlanner::destroy($lp_data->id);
            }
        }
        Toastr::success('Operation successful', 'Success');
        return redirect()->route('lesson');
    }

    public function deleteLessonItem($id)
    {
        try {
            $lesson = SmLesson::find($id);
            $lesson->delete();
            $SmLessonTopic = SmLessonTopic::where('lesson_id', $id)->get();
            if ($SmLessonTopic) {
                foreach ($SmLessonTopic as $t_data) {
                    SmLessonTopic::destroy($t_data->id);
                }
            }
            $SmLessonTopicDetail = SmLessonTopicDetail::where('lesson_id', $id)->get();
            if ($SmLessonTopicDetail) {
                foreach ($SmLessonTopicDetail as $td_data) {
                    SmLessonTopicDetail::destroy($td_data->id);
                }
            }
            $LessonPlanner = LessonPlanner::where('lesson_id', $id)->get();
            if ($LessonPlanner) {
                foreach ($LessonPlanner as $lp_data) {
                    LessonPlanner::destroy($lp_data->id);
                }
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->route('lesson');
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function lessonPlanner()
    {
        return view('lesson::lesson.lesson_planner');
    }

    public function loadLesson()
    {
        $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
        $subjects = SmAssignSubject::select('subject_id')
        ->where('teacher_id', $teacher_info->id)->get();

        $data['subjects'] = SmSubject::where('active_status', 1)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', Auth::user()->church_id)->get();
        $data['sections'] = SmSection::where('active_status', 1)
        ->where('church_year_id', getAcademicId())
        ->where('church_id', Auth::user()->church_id)->get();

        if (Auth::user()->role_id == 4) {
            $data['lessons'] = SmLesson::with('lessons', 'class', 'section', 'subject')
            ->whereIn('subject_id', $subjects)->statusCheck()
            ->groupBy(['age_group_id', 'mgender_id', 'subject_id'])->get();
        } else {
            $data['lessons'] = SmLesson::with('lessons', 'class', 'section', 'subject')
                ->statusCheck()
                ->groupBy(['age_group_id', 'mgender_id', 'subject_id'])->get();
        }
        if (!teacherAccess()) {
            $data['classes'] = SmClass::where('active_status', 1)
            ->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)->get();
        } else {
            $data['classes'] = SmAssignSubject::where('teacher_id', $teacher_info->id)
                ->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                ->where('sm_assign_subjects.church_year_id', getAcademicId())
                ->where('sm_assign_subjects.active_status', 1)
                ->groupBy('age_group_id')
                ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                ->select('sm_classes.id', 'age_group_name')
                ->get();
        }
        return $data;
    }
}

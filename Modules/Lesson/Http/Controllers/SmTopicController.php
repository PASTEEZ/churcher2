<?php

namespace Modules\Lesson\Http\Controllers;

use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\SmSubject;
use App\YearCheck;
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
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmTopicController extends Controller
{
    public function __construct()
    {
        $this->middleware('PM');
    }

    public function index()
    {
        try {
            $data = $this->loadTopic();
            if (moduleStatusCheck('University')) {
                return view('university::topic.topic', $data);
            } else {
                return view('lesson::topic.topic', $data);
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }
    public function store(Request $request)
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
                    'lesson' => 'required',
                ],
            );
        } else {
            $request->validate(
                [
                    'class' => 'required',
                    'subject' => 'required',
                    'section' => 'required',
                    'lesson' => 'required',
                ],
            );
        }
        DB::beginTransaction();
        if (moduleStatusCheck('University')) {
            $is_duplicate = SmLessonTopic::where('church_id', Auth::user()->church_id)
                                        ->where('un_session_id', $request->un_session_id)
                                        ->when($request->un_faculty_id, function ($query) use ($request) {
                                            $query->where('un_faculty_id', $request->un_faculty_id);
                                        })->where('un_department_id', $request->un_department_id)
                                        ->where('un_church_year_id', $request->un_church_year_id)
                                        ->where('un_semester_id', $request->un_department_id)
                                        ->where('un_semester_label_id', $request->un_church_year_id)
                                        ->where('un_subject_id', $request->un_subject_id)
                                        ->where('lesson_id', $request->lesson)
                                        ->first();
        } else {
            $is_duplicate = SmLessonTopic::where('church_id', Auth::user()->church_id)
                                        ->where('age_group_id', $request->class)
                                        ->where('lesson_id', $request->lesson)
                                        ->where('mgender_id', $request->section)
                                        ->where('subject_id', $request->subject)
                                        ->where('church_year_id', getAcademicId())
                                        ->first();
        }

        if ($is_duplicate) {
            $length = count($request->topic);
            for ($i = 0; $i < $length; $i++) {
                $topicDetail = new SmLessonTopicDetail;
                $topic_title = $request->topic[$i];
                $topicDetail->topic_id = $is_duplicate->id;
                $topicDetail->topic_title = $topic_title;
                $topicDetail->lesson_id = $request->lesson;
                $topicDetail->church_id = Auth::user()->church_id;
                if(moduleStatusCheck('University')){
                    $topicDetail->un_church_year_id = getAcademicId();
                }else{
                    $topicDetail->church_year_id = getAcademicId();
                }
                $topicDetail->save();
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        } else {
            try {
                $smTopic = new SmLessonTopic;
                $smTopic->age_group_id = $request->class;
                $smTopic->mgender_id = $request->section;
                $smTopic->subject_id = $request->subject;
                $smTopic->lesson_id = $request->lesson;
                $smTopic->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $smTopic->church_id = Auth::user()->church_id;
                if (moduleStatusCheck('University')) {
                    $common = App::make(UnCommonRepositoryInterface::class);
                    $common->storeUniversityData($smTopic, $request);
                }else{
                    $smTopic->church_year_id = getAcademicId();
                }
                $smTopic->save();
                $smTopic_id = $smTopic->id;
                $length = count($request->topic);
                for ($i = 0; $i < $length; $i++) {
                    $topicDetail = new SmLessonTopicDetail;
                    $topic_title = $request->topic[$i];
                    $topicDetail->topic_id = $smTopic_id;
                    $topicDetail->topic_title = $topic_title;
                    $topicDetail->lesson_id = $request->lesson;
                    $topicDetail->church_id = Auth::user()->church_id;
                    if(!moduleStatusCheck('University')){
                        $topicDetail->church_year_id = getAcademicId();
                    }
                    $topicDetail->save();
                }
                DB::commit();

                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } catch (\Exception $e) {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
    }

    public function edit($id)
    {

        try {
            $data = $this->loadTopic();
            $data['topic'] = SmLessonTopic::where('church_year_id', getAcademicId())
            ->where('id', $id)->where('church_id', Auth::user()->church_id)->first();
            $data['lessons'] = SmLesson::where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
            $data['topicDetails'] = SmLessonTopicDetail::where('topic_id', $data['topic']->id)->where('church_year_id', getAcademicId())
            ->where('church_id', Auth::user()->church_id)->get();
            if (moduleStatusCheck('University')) {

                $request = [
                    'semester_id' => $data['topic']->un_semester_id,
                    'church_year_id' => $data['topic']->un_church_year_id,
                    'session_id' => $data['topic']->un_session_id,
                    'department_id' => $data['topic']->un_department_id,
                    'faculty_id' => $data['topic']->un_faculty_id,
                    'semester_label_id' => $data['topic']->un_semester_label_id,
                    'subject_id' => $data['topic']->un_subject_id,
                ];
                $interface = App::make(UnCommonRepositoryInterface::class);
              
                $data += $interface->getCommonData($data['topic']);
                return view('university::topic.edit_topic', $data);
            }
            return view('lesson::topic.editTopic', $data);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }
    public function updateTopic(Request $request)
    {
   
        try {
            $length = count($request->topic);
            for ($i = 0; $i < $length; $i++) {
                $topicDetail = SmLessonTopicDetail::find($request->topic_detail_id[$i]);
                $topic_title = $request->topic[$i];
                $topicDetail->topic_title = $topic_title;
                $topicDetail->church_id = Auth::user()->church_id;
                $topicDetail->church_year_id = getAcademicId();
                $topicDetail->save();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect('/lesson/topic');

        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }
    public function topicdelete($id)
    {
        $topic = SmLessonTopic::find($id);
        $topic->delete();
        $topicDetail = SmLessonTopicDetail::where('topic_id', $id)->get();
        if ($topicDetail) {
            foreach ($topicDetail as $data) {
                SmLessonTopicDetail::destroy($data->id);
                LessonPlanner::where('topic_detail_id', $data->id)->get();
            }
        }

        $topicLessonPlan = LessonPlanner::where('topic_id', $id)->get();
        if ($topicLessonPlan) {
            foreach ($topicLessonPlan as $topic_data) {
                LessonPlanner::destroy($topic_data->id);
            }
        }

        Toastr::success('Operation successful', 'Success');
        return redirect()->route('lesson.topic');

    }
    public function deleteTopicTitle($id)
    {
        SmLessonTopicDetail::destroy($id);
        $topicDetail = LessonPlanner::where('topic_detail_id', $id)->get();
        if ($topicDetail) {
            foreach ($topicDetail as $data) {
                LessonPlanner::destroy($data->id);
            }
        }

        Toastr::success('Operation successful', 'Success');
        return redirect()->back();
    }
    public function loadTopic()
    {
        $teacher_info = SmStaff::where('user_id', Auth::user()->id)->first();
        if (Auth::user()->role_id == 4) {
            $subjects = SmAssignSubject::select('subject_id')->where('teacher_id', $teacher_info->id)->get();
            $data['topics'] = SmLessonTopic::with('lesson', 'class', 'section', 'subject')->whereIn('subject_id', $subjects)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();

        } else {
            $data['topics'] = SmLessonTopic::with('lesson', 'class', 'section', 'subject')->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        }

        if (!teacherAccess()) {
            $data['classes'] = SmClass::where('active_status', 1)->where('church_year_id', getAcademicId())->where('church_id', Auth::user()->church_id)->get();
        } else {
            $data['classes'] = SmAssignSubject::where('teacher_id', $teacher_info->id)
                ->join('sm_classes', 'sm_classes.id', 'sm_assign_subjects.age_group_id')
                ->where('sm_assign_subjects.active_status', 1)
                ->where('sm_assign_subjects.church_id', Auth::user()->church_id)
                ->where('sm_assign_subjects.church_year_id', getAcademicId())
                ->select('sm_classes.id', 'age_group_name')
                ->get();
        }
        $data['subjects'] = SmSubject::get();
        $data['sections'] = SmSection::get();
        return $data;
    }
}

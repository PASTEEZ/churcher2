<?php

namespace Database\Seeders\Lesson;

use App\SmAssignSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Lesson\Entities\SmLesson;
use Modules\Lesson\Entities\SmLessonTopic;
use Modules\Lesson\Entities\SmLessonTopicDetail;

class SmTopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        // $topic = ['theory', 'poem', 'practical', 'others'];
        // $lesson_id = SmLesson::where('age_group_id', 1)->where('mgender_id', 1)->where('church_id', $church_id)->where('church_year_id', $church_year_id)->first()->id;
        // $assignSubject = SmAssignSubject::where('church_id', $church_id)
        // ->where('church_year_id', $church_year_id)
        // ->first();
        // $is_duplicate = SmLessonTopic::where('age_group_id', $assignSubject->age_group_id)->where('lesson_id', $lesson_id)->where('mgender_id', $assignSubject->sction_id)->where('subject_id', $assignSubject->subject_id)->first();
        // if ($is_duplicate) {
        //     $length = count($topic);
        //     for ($i = 0; $i < $length; $i++) {
        //         $topic_title = $topic[$i++];
  
        //         $topicDetail = new SmLessonTopicDetail;
        //         $topicDetail->topic_id = $is_duplicate->id;
        //         $topicDetail->topic_title = $topic_title ? $topic_title.'0'.$i : '0'.$i;
        //         $topicDetail->lesson_id = $lesson_id;
        //         $topicDetail->church_id = $church_id;
        //         $topicDetail->church_year_id = $church_year_id;
        //         $topicDetail->save();
  
        //     }
        //     DB::commit();
  
        // } else {
  
        //     $smTopic = new SmLessonTopic;
        //     $smTopic->age_group_id = $assignSubject->age_group_id;
        //     $smTopic->mgender_id = $assignSubject->mgender_id;
        //     $smTopic->subject_id = $assignSubject->subject_id;
        //     $smTopic->lesson_id = $lesson_id;
        //     $smTopic->church_id = $church_id;
        //     $smTopic->church_year_id = $church_year_id;
        //     $smTopic->save();
        //     $smTopic_id = $smTopic->id;
        //     $length = count($topic);
  
        //     for ($i = 0; $i < $length; $i++) {
        //         $topic_title = $topic[$i];
  
        //         $topicDetail = new SmLessonTopicDetail;
        //         $topicDetail->topic_id = $smTopic_id;
        //         $topicDetail->topic_title = $topic_title ? $topic_title.'0'.$i : '0'.$i;
        //         $topicDetail->lesson_id = $lesson_id;
        //         $topicDetail->church_id = $church_id;
        //         $topicDetail->church_year_id = $church_year_id;
        //         $topicDetail->save();
  
        //     }
        //     DB::commit();
  
        // }
    }
}

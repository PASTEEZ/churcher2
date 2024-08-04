<?php

namespace Database\Seeders\Lesson;

use App\SmWeekend;
use Illuminate\Database\Seeder;
use Modules\Lesson\Entities\SmLesson;
use Modules\Lesson\Entities\LessonPlanner;
use Modules\Lesson\Entities\SmLessonTopicDetail;

class SmLessonPlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count)
    {
        //
        $days = SmWeekend::where('church_id', $church_id)->get();
        $lesson_id = SmLesson::where('church_id', $church_id)
                                ->where('church_year_id', $church_year_id)
                                ->value('id');
        $topic_id = SmLessonTopicDetail::where('lesson_id', $lesson_id)
                                        ->where('church_id', $church_id)
                                        ->where('church_year_id', $church_year_id)
                                        ->value('topic_id');
        foreach($days as $day) {
            $lessonPlanner = new LessonPlanner;
            $lessonPlanner->day = $day->id;
            $lessonPlanner->lesson_detail_id = $lesson_id;
            $lessonPlanner->lesson_id = $lesson_id;
            $lessonPlanner->topic_id = $topic_id;
            $lessonPlanner->sub_topic = $day->name;
            $lessonPlanner->church_id=$church_id;
            $lessonPlanner->church_year_id=$church_year_id;
            $lessonPlanner->save();
        }

    }
}

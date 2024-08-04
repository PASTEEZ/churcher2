<?php

namespace Database\Seeders\OnlineExam;

use App\SmOnlineExam;
use App\SmQuestionBank;
use Illuminate\Database\Seeder;
use App\SmOnlineExamQuestionAssign;

class SmOnlineExamQuestionAssignTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count)
    {
        $online_exams = SmOnlineExam::where('church_id', $church_id)->where('church_year_id', $church_year_id)->take(10)->get();
        foreach ($online_exams as $online_exam){
            $question_banks = SmQuestionBank::where('church_id', $church_id)->where('church_year_id', $church_year_id)->take(10)->get();
            foreach ($question_banks as $question_bank) {
                $store = new SmOnlineExamQuestionAssign();
                $store->online_exam_id = $online_exam->id;
                $store->question_bank_id = $question_bank->id;
                $store->created_at = date('Y-m-d h:i:s');
                $store->church_id = $church_id;
                $store->church_year_id = $church_year_id;
                $store->save();
            }

        }
    }
}

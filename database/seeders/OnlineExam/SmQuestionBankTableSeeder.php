<?php

namespace Database\Seeders\OnlineExam;

use App\SmQuestionBank;
use App\SmAssignSubject;
use App\SmQuestionGroup;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SmQuestionBankTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        {
            $faker = Faker::create();
            $i = 1;
            $group_id = SmQuestionGroup::where('church_id', $church_id)->where('church_year_id', $church_year_id)->value('id');
            $question_details = SmAssignSubject::all();
            foreach ($question_details as $question_detail) {
    
                $store = new SmQuestionBank();
                $store->q_group_id = $group_id;
                $store->age_group_id = $question_detail->age_group_id;
                $store->mgender_id = $question_detail->mgender_id;
                $store->type = 'M';
                $store->question = $faker->realText($maxNbChars = 80, $indexSize = 1);
                $store->marks = 100;
                $store->trueFalse = 'T';
                $store->suitable_words = $faker->realText($maxNbChars = 50, $indexSize = 1);
                $store->number_of_option = 4;
                $store->created_at = date('Y-m-d h:i:s');
                $store->church_id = $church_id;
                $store->church_year_id = $church_year_id;
                $store->save();
            }
        }
    }
}

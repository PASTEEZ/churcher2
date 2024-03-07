<?php

namespace Database\Seeders\Library;

use App\SmBook;
use App\SmBookCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmBookCategoriesTableSeeder extends Seeder
{
    public function run($church_id = 1, $count = 16){

        SmBookCategory::factory()->times($count)->create([
            'church_id' => $church_id,
        ])->each(function ($book_category){
            SmBook::factory()->times(11)->create([
               'church_id' => $book_category->church_id,
            ]);
        });
    }
}
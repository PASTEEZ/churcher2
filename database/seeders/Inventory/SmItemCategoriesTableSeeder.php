<?php

namespace Database\Seeders\Inventory;

use App\SmItem;
use App\SmItemCategory;
use Illuminate\Database\Seeder;

class SmItemCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($church_id, $church_year_id, $count=5)
    {
        //
        $school_academic=[
            'church_id'=>$church_id,
            'church_year_id'=>$church_year_id,
        ];
        SmItemCategory::factory()->times($count)->create($school_academic)->each(function ($itemCategory) use($school_academic, $count){
            SmItem::factory()->times($count)->create(array_merge([
                'item_category_id' =>$itemCategory->id,
            ],$school_academic));
        });
    }
}

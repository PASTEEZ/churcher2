<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmExamTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_exam_types', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('active_status')->default(1);
            $table->string('title', 255);
            $table->float('percentage')->nullable();
            $table->timestamps();
            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
            
            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        // DB::table('sm_exam_types')->insert([

        //     [
        //         'church_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'First Term'
        //     ],
        //     [
        //         'church_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Second Term'
        //     ],
        //     [
        //         'church_id'=> 1,
        //         'active_status'=> 1,
        //         'title' => 'Third Term'
        //     ],

        //    ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_exam_types');
    }
}

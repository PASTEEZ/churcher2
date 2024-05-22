<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\ExamPlan\Entities\SeatPlanSetting;

class CreateSeatPlanSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_plan_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('church_name')->nullable();
            $table->boolean('student_photo')->nullable();
            $table->boolean('member_name')->nullable();
            $table->boolean('registration_no')->nullable();
            $table->boolean('class_section')->nullable();
            $table->boolean('exam_name')->nullable();
            $table->boolean('roll_no')->nullable();
            $table->boolean('church_year')->nullable();
            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->timestamps();
        });

        $setting = SeatPlanSetting::first();
        if(!$setting){
            $setting = new SeatPlanSetting();
            $setting->student_photo = 1; 
            $setting->member_name = 1;
            $setting->registration_no = 1;
            $setting->class_section = 1;
            $setting->exam_name = 1;
            $setting->church_year = 1;
            $setting->roll_no = 1;
            $setting->church_name = 1;
            $setting->save();
        }
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seat_plan_settings');
    }
}

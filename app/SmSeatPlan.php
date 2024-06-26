<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSeatPlan extends Model
{
    use HasFactory;
    public function seatPlanChild(){
    	return $this->hasMany('App\SmSeatPlanChild', 'seat_plan_id', 'id');
    }
    public function exam(){
    	return $this->belongsTo('App\SmExam', 'exam_id', 'id');
    }
    public function subject(){
    	return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }
    public function section(){
    	return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }
    public function class(){
    	return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public static function total_student($class, $section){
        try {
            return SmStudent::where('age_group_id', $class)->where('mgender_id', $section)->count();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
}

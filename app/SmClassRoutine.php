<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmClassRoutine extends Model
{
    use HasFactory;
    public function subject(){
    	return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

    public function class(){
    	return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }

    public function section(){
    	return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }
    
    public static function teacherId($age_group_id, $mgender_id, $subject_id){
    	
        try {
            return SmAssignSubject::where('age_group_id', $age_group_id)->where('mgender_id', $mgender_id)->where('subject_id', $subject_id)->first();
        } catch (\Exception $e) {
            $data=[];
            return $data;
        }
    }
}

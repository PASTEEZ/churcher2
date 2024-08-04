<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmExamAttendance extends Model
{
    use HasFactory;
    public function examAttendanceChild()
    {
        return $this->hasMany('App\SmExamAttendanceChild', 'exam_attendance_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    // public function scopesClassSection($query){
    //     return $query->where('age_group_id',request()->age_group_id)->where('mgender_id',request()->mgender_id)->where('subject_id',request()->subject_id);
    // }
}

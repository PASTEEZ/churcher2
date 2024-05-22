<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmAssignClassTeacher extends Model
{
    use HasFactory;
    public function class()
    {
        return $this->belongsTo('App\SmClass', 'age_group_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id');
    }

    public function classTeachers()
    {
        return $this->hasMany('App\SmClassTeacher', 'assign_class_teacher_id', 'id');
    }

    public function scopeStatus($query)
    {
        return $query->where('active_status', 1)->where('church_id', Auth::user()->church_id);
    }

}

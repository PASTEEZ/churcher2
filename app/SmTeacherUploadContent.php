<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmTeacherUploadContent extends Model
{
    use HasFactory;

    public function contentTypes()
    {
        return $this->belongsTo('App\SmContentType', 'content_type', 'id');
    }

    public function roles()
    {
        return $this->belongsTo('Modules\RolePermission\Entities\InfixRole', 'available_for', 'id');
    }

    public function classes()
    {
        return $this->belongsTo('App\SmClass', 'class', 'id');
    }
    public function sections()
    {
        return $this->belongsTo('App\SmSection', 'section', 'id');
    }
    public function users()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    
    public function unSession()
    {
        return $this->belongsTo('Modules\University\Entities\UnSession', 'un_session_id', 'id')->withDefault();
    }

    public function unSection()
    {
        return $this->belongsTo('App\SmSection', 'un_mgender_id', 'id')->withDefault();
    }

    public function unFaculty()
    {
        return $this->belongsTo('Modules\University\Entities\UnFaculty', 'un_faculty_id', 'id')->withDefault();
    }
    public function unDepartment()
    {
        return $this->belongsTo('Modules\University\Entities\UnDepartment', 'un_department_id', 'id')->withDefault();
    }
    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_church_year_id', 'id')->withDefault();
    }
    public function unSemester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }


    public function semester()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemester', 'un_semester_id', 'id')->withDefault();
    }

    public function semesterLabel()
    {
        return $this->belongsTo('Modules\University\Entities\UnSemesterLabel', 'un_semester_label_id', 'id')->withDefault();
    }
}

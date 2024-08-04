<?php

namespace App\Scopes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;

class AcademicSchoolScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $table=$model->getTable();
        $columns = Schema::getColumnListing($table);
        $academicId = moduleStatusCheck('University') && in_array('un_church_year_id', $columns)
        ? '.un_church_year_id' : '.church_year_id';
        if (Auth::check()) {
            if (app()->bound('school')) {
                if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == "yes" && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1) {
                    $builder->where($table. $academicId, getAcademicId());
                } else {
                    $builder->where($table. $academicId, getAcademicId())->where($table.'.church_id', app('school')->id);
                }
            } elseif (Auth::check()) {
                if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == "yes" && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1) {
                    $builder->where($table. $academicId, getAcademicId());
                } else {
                    $builder->where($table. $academicId, getAcademicId())->where($table.'.church_id', Auth::user()->church_id);
                }
            }
        } else {
            $builder->where($table.'.church_id', 1);
        }
    }
}

<?php

namespace App\Scopes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;

class StatusAcademicSchoolScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $academicId = moduleStatusCheck('University') && in_array('un_church_year_id', $columns) 
        ? '.un_church_year_id' : '.church_year_id';
        if (Auth::check()) {
            $academic = getAcademicId();
            if (app()->bound('school')) {
                if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == "yes" && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1) {
                    $builder->where($table . '.active_status', 1)->where($table . $academicId, getAcademicId());
                } else {
                    $builder->where($table . '.active_status', 1)->where($table . $academicId, getAcademicId())->where($table . '.church_id', Auth::user()->church_id);
                }
            } else {
                if (moduleStatusCheck('Saas') == true && Auth::user()->is_administrator == "yes" && Session::get('isSchoolAdmin') == false && Auth::user()->role_id == 1) {
                    $builder->where($table . '.active_status', 1)->where($table . $academicId, getAcademicId());
                } else {
                    $builder->where($table . '.active_status', 1)->where($table . $academicId, getAcademicId())->where($table . '.church_id', Auth::user()->church_id);
                }
            }
        } else {
            $builder->where($table . '.active_status', 1);
        }
    }
}

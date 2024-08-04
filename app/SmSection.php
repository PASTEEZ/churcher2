<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSection extends Model
{
    //
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        //static::addGlobalScope(new StatusAcademicSchoolScope);
    }

    public function students()
    {
        return $this->hasMany('App\SmStudent', 'mgender_id', 'id');
    }
    public function unAcademic()
    {
        return $this->belongsTo('Modules\University\Entities\UnAcademicYear', 'un_church_year_id', 'id')->withDefault();
    }
}

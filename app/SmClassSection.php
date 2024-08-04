<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\StatusAcademicSchoolScope;

class SmClassSection extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();
  
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
    
    
    public function sectionName()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id')->withDefault();
    }
    public function students()
    {
        return $this->hasMany('App\SmStudent', 'mgender_id', 'mgender_id');
    }
    public function sectionWithoutGlobal()
    {
        return $this->belongsTo('App\SmSection', 'mgender_id', 'id')->withoutGlobalScopes()->withDefault();
    }
}

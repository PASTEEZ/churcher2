<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmStudentPromotion extends Model
{
    public function student(){
        return $this->belongsTo('App\SmStudent', 'member_id', 'id');
    }

    public function class(){
		return $this->belongsTo('App\SmClass', 'previous_age_group_id', 'id');
    }
    
}

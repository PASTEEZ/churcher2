<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmOptionalSubjectAssign extends Model
{
    use HasFactory;

    public static function is_optional_subject($member_id, $subject_id)
    {
        try {
            $result = SmOptionalSubjectAssign::where('member_id', $member_id)->where('subject_id', $subject_id)->first();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    public function subject()
    {
        return $this->belongsTo('App\SmSubject', 'subject_id', 'id');
    }

}

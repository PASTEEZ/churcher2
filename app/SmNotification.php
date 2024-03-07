<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SmNotification extends Model
{
    use HasFactory;
    public static function notifications()
    {

        $user = Auth()->user();
        if ($user) {
            return SmNotification::where('user_id', $user->id)->where('is_read', 0)
                ->orderBy('id', 'DESC')
                ->where('church_id', $user->church_id)
                ->where(function($q){
                    $q->where('church_year_id', getAcademicId())->orWhereNull('church_year_id');
                })
                ->get();
        }

    }
}

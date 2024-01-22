<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberData extends Model
{
    protected $table = 'sm_students';
    protected $fillable = [
        'first_name', 'last_name', 'date_of_birth'
    ];
}

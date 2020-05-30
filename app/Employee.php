<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'full_name',
        'nick_name',
        'age',
        'birth_date',
        'address',
        'mobile',
        'avatar',
        'created_by',
        'modify_by',
        'created_at',
        'updated_at',
    ];
}

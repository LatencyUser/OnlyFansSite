<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserCode extends Model
{

    public $table = "user_codes";

    protected $fillable = [
        'user_id',
        'code',
    ];
}

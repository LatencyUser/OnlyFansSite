<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{

    public $table = "user_devices";

    protected $fillable = [
        'user_id',
        'address',
        'agent',
        'signature',
        'verified_at'
    ];
}

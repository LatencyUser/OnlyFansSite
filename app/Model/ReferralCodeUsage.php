<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReferralCodeUsage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'used_by', 'referral_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /*
     * Relationships
     */

    public function usedBy()
    {
        return $this->hasOne('App\User', 'id', 'used_by');
    }
}

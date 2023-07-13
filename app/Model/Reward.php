<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    const FEE_PERCENTAGE_REWARD_TYPE = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_user_id', 'to_user_id', 'transaction_id', 'reward_type', 'referral_code_usage_id', 'amount'
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
    public function fromUser()
    {
        return $this->hasOne('App\User', 'id', 'from_user_id');
    }

    public function toUser()
    {
        return $this->hasOne('App\User', 'id', 'to_user_id');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Model\Transaction', 'transaction_id');
    }

    public function referralCodeUsage()
    {
        return $this->belongsTo('App\Model\ReferralCodeUsage', 'referral_code_usage_id');
    }
}

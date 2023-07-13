<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{


    const PENDING_STATUS = 0;
    const APPROVED_STATUS = 1;
    const DISAPPROVED_STATUS = 2;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'text',
        'price',
        'status',
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

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Model\PostComment');
    }

    public function reactions()
    {
        return $this->hasMany('App\Model\Reaction');
    }

    public function bookmarks()
    {
        return $this->hasMany('App\Model\UserBookmark');
    }

    public function attachments()
    {
        return $this->hasMany('App\Model\Attachment');
    }

    public function transactions()
    {
        return $this->hasMany('App\Model\Transaction');
    }

    public function postPurchases()
    {
        return $this->hasMany('App\Model\Transaction', 'post_id', 'id')->where('status', 'approved')->where('type', 'post-unlock');
    }

    public function tips()
    {
        return $this->hasMany('App\Model\Transaction')->where('type', 'tip')->where('status', 'approved');
    }

    public static function getStatusName($status){
        switch ($status){
            case self::PENDING_STATUS:
                return __("pending");
                break;
            case self::APPROVED_STATUS:
                return __("approved");
                break;
            case self::DISAPPROVED_STATUS:
                return __("disapproved");
                break;
        }
    }

}

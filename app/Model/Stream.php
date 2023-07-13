<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Stream extends Model
{
    /**
     * Streaming is currently playing
     */
    const IN_PROGRESS_STATUS = 'in-progress';
    /**
     * Streaming ended
     */
    const ENDED_STATUS = 'ended';

    /**
     * Stream deleted
     */
    const DELETED_STATUS = 'deleted';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'status', 'name', 'slug', 'poster', 'pushr_id', 'hls_link', 'vod_link', 'rtmp_server', 'rtmp_key', 'price', 'requires_subscription', 'sent_expiring_reminder', 'is_public', 'settings'
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
        'ended_at' => 'datetime',
        'settings' => 'array',
    ];

    public function getPosterAttribute($value)
    {
        if($value){
            if(getSetting('storage.driver') == 's3'){
                return 'https://'.getSetting('storage.aws_bucket_name').'.s3.'.getSetting('storage.aws_region').'.amazonaws.com/'.$value;
            }
            elseif(getSetting('storage.driver') == 'wasabi' || getSetting('storage.driver') == 'do_spaces'){
                return Storage::url($value);
            }
            else{
                return Storage::disk('public')->url($value);
            }
        }else{
            return asset('/img/live-stream-cover.svg');
        }
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany('App\Model\StreamMessage');
    }

    public function streamPurchases()
    {
        return $this->hasMany('App\Model\Transaction', 'stream_id', 'id')->where('status', 'approved')->where('type', 'stream-access');
    }


}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StreamMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'stream_id', 'message'];

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
        return $this->belongsTo('App\User', 'user_id');
    }

    public function stream()
    {
        return $this->belongsTo('App\Model\Stream', 'stream_id')->orderBy('created_at', 'desc');
    }

}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    const DEPOSIT_TYPE = 'deposit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'transaction_id', 'status', 'type', 'reason', 'message', 'amount'
    ];

    protected $appends = ['files'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Get request files path as array
     */
    public function getFilesAttribute(){
        $files = [];
        $attachments = $this->attachments();
        if($attachments && count($attachments)){
            foreach ($attachments as $attachment){
                $files[] = $attachment['filename'];
            }
        }
        return $files;
    }

    public function attachments()
    {
        return $this->hasMany('App\Model\Attachment');
    }
}

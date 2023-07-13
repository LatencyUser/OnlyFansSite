<?php

namespace App\Observers;

use App\Model\Attachment;
use App\Model\UserMessage;
use App\Providers\AttachmentServiceProvider;
use Illuminate\Support\Facades\Log;

class UserMessagesObserver
{
    /**
     * Listen to the Attachment deleted event.
     *
     * @param  \App\Model\UserMessage  $userMessage
     * @return void
     */
    public function deleting(UserMessage $userMessage)
    {
        if ($userMessage->attachments()) {
            $userMessage->attachments()->each(function (Attachment $attachment) {
                try {
                    AttachmentServiceProvider::removeAttachment($attachment);
                } catch (\Exception $exception) {
                    Log::error("Failed deleting files for attachment: " . $attachment->id . ", e: " . $exception->getMessage());
                }
            });
        }
    }
}

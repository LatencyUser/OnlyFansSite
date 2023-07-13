<?php

namespace App\Observers;

use App\Model\Attachment;
use App\Providers\AttachmentServiceProvider;
use Illuminate\Support\Facades\Log;

class AttachmentsObserver
{
    /**
     * Listen to the Attachment deleted event.
     *
     * @param  \App\Model\Attachment  $attachment
     * @return void
     */
    public function deleted(Attachment $attachment)
    {
        try {
            AttachmentServiceProvider::removeAttachment($attachment);
        } catch (\Exception $exception) {
            Log::error("Failed deleting files for attachment: " . $attachment->id . ", e: " . $exception->getMessage());
        }
    }
}

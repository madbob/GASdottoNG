<?php

namespace App\Observers;

use App\Attachment;

class AttachmentObserver
{
    public function deleted(Attachment $attachment)
    {
        @unlink($attachment->path);
    }
}

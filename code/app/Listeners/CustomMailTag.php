<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;
use Symfony\Component\Mailer\Header\TagHeader;

class CustomMailTag
{
    public function handle(MessageSending $event)
    {
		if (env('MAIL_MAILER') == 'sendinblue') {
			$event->message->getHeaders()->add(new TagHeader('gasdotto'));
		}

		return true;
    }
}

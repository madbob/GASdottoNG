<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
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

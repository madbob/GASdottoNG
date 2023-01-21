<?php

namespace App\Notifications;

trait MailFormatter
{
    public function formatMail($message, $user, $config_name, $params = [])
    {
		$type = systemParameters('MailTypes')[$config_name];
        $config = json_decode($user->gas->getConfig('mail_' . $config_name));

		if (isset($params['user']) == false) {
			$params['user'] = $user;
		}

        $subject = $type->formatText($config->subject, $user->gas, $params);
        $body = $type->formatText($config->body, $user->gas, $params);

        $message->subject($subject)->view('emails.empty', ['content' => $body]);
        return $message;
    }
}

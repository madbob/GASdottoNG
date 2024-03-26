<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Collection;

use App\Gas;

trait MailFormatter
{
    public function formatMail($message, $user, $config_name, $params = [])
    {
		$type = systemParameters('MailTypes')[$config_name];

        /*
            In alcune circostanze il destinatario della mail non è uno User ma,
            ad esempio, un Supplier, il quale nella relazione "gas" non ha un
            singolo elemento ma una Collection. Qui arbitrariamente decido di
            usare le configurazioni del primo GAS assegnato
        */
        $gas = $user->gas;
        if (is_a($gas, Collection::class)) {
            $gas = $gas->first();
        }

        $config = json_decode($gas->getConfig('mail_' . $config_name));

		if (isset($params['user']) == false) {
			$params['user'] = $user;
		}

        $subject = $type->formatText($config->subject, $gas, $params);
        $body = $type->formatText($config->body, $gas, $params);

        $message->subject($subject)->view('emails.empty', ['content' => $body]);
        return $message;
    }
}

<?php

/*
    Per ciascuna tipologia di email si assume che esista una configurazione (in
    App\Parameters\Config) che abbia come identificativo lo stesso della mail
    preceduto dal suffisso 'mail_'
*/

namespace App\Parameters\MailTypes;

use App\Parameters\Parameter;

abstract class MailType extends Parameter
{
    public abstract function description();
    public abstract function params();
    public abstract function enabled($gas);

	public function formatParams()
	{
		$mail_params = [];

		foreach($this->params() as $placeholder => $placeholder_description) {
			$mail_params[] = sprintf('%%[%s]: %s', $placeholder, $placeholder_description);
		}

		return join('<br>', $mail_params);
	}

	/*
		MailType autocompleta alcuni parametri che non sono già stati passati
		in sede di formattazione.
		Alcuni parametri sono compilati a partire dai Model passati
		esplicitamente: arrivando qui da MailFormatter viene già popolato
		"user", badare di inizializzarlo in altre circostanze
	*/
	private function fillParameters($gas, $params)
	{
		$params['gas_name'] = $gas->name;

		foreach($this->params() as $identifier => $name) {
			if (isset($params[$identifier]) == false) {
				$value = null;

				switch($identifier) {
					case 'username':
						$value = $params['user']->username;
						break;

					case 'gas_login_link':
						$value = route('login');
						break;

					case 'current_credit':
						$curr = defaultCurrency();
						$value = printablePriceCurrency($params['user']->currentBalanceAmount($curr));
						break;
				}

				if (is_null($value) == false) {
					$params[$identifier] = $value;
				}
			}
		}

		return $params;
	}

	public function formatText($text, $gas, $params)
    {
		$params = $this->fillParameters($gas, $params);

        foreach($params as $placeholder => $value) {
            $p = sprintf('%%[%s]', $placeholder);
            $text = str_replace($p, $value, $text);
        }

        return $text;
    }
}

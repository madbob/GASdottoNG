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
    abstract public function description();

    abstract public function params();

    abstract public function enabled($gas);

    public function formatParams()
    {
        $mail_params = [];

        foreach ($this->params() as $placeholder => $placeholder_description) {
            $mail_params[] = sprintf('%%[%s]: %s', $placeholder, $placeholder_description);
        }

        return implode('<br>', $mail_params);
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

        foreach (array_keys($this->params()) as $identifier) {
            if (isset($params[$identifier]) === false) {
                switch ($identifier) {
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

                    default:
                        $value = null;
                        break;
                }

                if ($value != null) {
                    $params[$identifier] = $value;
                }
            }
        }

        return $params;
    }

    public function formatText($text, $gas, $params)
    {
        $params = $this->fillParameters($gas, $params);

        foreach ($params as $placeholder => $value) {
            $p = sprintf('%%[%s]', $placeholder);
            $text = str_replace($p, $value, $text);
        }

        return $text;
    }
}

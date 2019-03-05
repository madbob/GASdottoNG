<?php

namespace App\Notifications;

trait MailFormatter
{
    private function formatText($text, $gas, $params)
    {
        $params['gas_name'] = $gas->name;

        foreach($params as $placeholder => $value) {
            $p = sprintf('%%[%s]', $placeholder);
            $text = str_replace($p, $value, $text);
        }

        return $text;
    }

    public function formatMail($message, $config_name, $params = [])
    {
        $gas = currentAbsoluteGas();

        $subject = $this->formatText($gas->getConfig("mail_${config_name}_subject"), $gas, $params);
        $body = $this->formatText($gas->getConfig("mail_${config_name}_body"), $gas, $params);
        $message->subject($subject)->view('emails.empty', ['content' => $body]);

        return $message;
    }
}

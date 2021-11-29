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
        $config = json_decode($gas->getConfig('mail_' . $config_name));

        $subject = $this->formatText($config->subject, $gas, $params);
        $body = $this->formatText($config->body, $gas, $params);
        $message->subject($subject)->view('emails.empty', ['content' => $body]);

        return $message;
    }
}

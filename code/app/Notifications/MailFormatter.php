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
        $configurations = $gas->custom_mails;

        if (!isset($configurations[$config_name])) {
            Log::error('Custom mail configuration not found: ' . $config_name);
            return null;
        }

        $subject = $this->formatText($configurations[$config_name]->subject, $gas, $params);
        $body = $this->formatText($configurations[$config_name]->body, $gas, $params);

        $message->subject($subject)->view('emails.empty', ['content' => $body]);

        return $message;
    }
}

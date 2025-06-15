<?php

namespace App\Parameters\Config;

class MailReceipt extends Config
{
    public function identifier()
    {
        return 'mail_receipt';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('texts.mail.receipt.defaults.subject'),
            'body' => __('texts.mail.receipt.defaults.body'),
        ];
    }
}

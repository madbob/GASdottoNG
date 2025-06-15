<?php

namespace App\Parameters\Config;

class MailApproved extends Config
{
    public function identifier()
    {
        return 'mail_approved';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => __('texts.mail.approved.defaults.subject'),
            'body' => __('texts.mail.approved.defaults.body'),
        ];
    }
}

<?php

namespace App\Parameters\Config;

class MailManualWelcome extends Config
{
    public function identifier()
    {
        return 'mail_manual_welcome';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'subject' => _i('Benvenuto!'),
            'body' => _i("Sei stato invitato a %[gas_name]!\n\nPer accedere la prima volta clicca il link qui sotto.\n%[gas_access_link]\n\nIn futuro potrai accedere usando quest'altro link, lo username \"%[username]\" e la password che avrai scelto.\n%[gas_login_link]\n"),
        ];
    }
}

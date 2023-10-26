<?php

namespace App\Parameters\MailTypes;

class Declined extends MailType
{
    public function identifier()
    {
        return 'declined';
    }

    public function description() {
        return _i('Messaggio inviato agli iscritti non approvati.');
    }

    public function params() {
        return [];
    }

    public function enabled($gas) {
        return $gas->hasFeature('public_registrations') && $gas->public_registrations['manual'] == true;
    }
}

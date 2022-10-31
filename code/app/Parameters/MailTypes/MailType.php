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
}

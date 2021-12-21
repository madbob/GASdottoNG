<?php

namespace App\View\Texts;

class Months
{
    private static function it()
    {
        return [
            'gennaio' => 'january',
            'febbraio' => 'february',
            'marzo' => 'march',
            'aprile' => 'april',
            'maggio' => 'may',
            'giugno' => 'june',
            'luglio' => 'july',
            'agosto' => 'august',
            'settembre' => 'september',
            'ottobre' => 'october',
            'novembre' => 'november',
            'dicembre' => 'december',
        ];
    }

    private static function en()
    {
        return [
            'january' => 'january',
            'february' => 'february',
            'march' => 'march',
            'april' => 'april',
            'may' => 'may',
            'june' => 'june',
            'july' => 'july',
            'august' => 'august',
            'september' => 'september',
            'october' => 'october',
            'november' => 'november',
            'december' => 'december',
        ];
    }

    private static function de()
    {
        return [
            'januar' => 'january',
            'februar' => 'february',
            'märz' => 'march',
            'april' => 'april',
            'mai' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'august' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'dezember' => 'december',
        ];
    }

    private static function fr()
    {
        return [
            'janvier' => 'january',
            'février' => 'february',
            'mars' => 'march',
            'avril' => 'april',
            'mai' => 'may',
            'juin' => 'june',
            'juillet' => 'july',
            'août' => 'august',
            'septembre' => 'september',
            'octobre' => 'october',
            'novembre' => 'november',
            'décembre' => 'december',
        ];
    }

    private static function nb()
    {
        return [
            'januar' => 'january',
            'februar' => 'february',
            'märs' => 'march',
            'april' => 'april',
            'mai' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'august' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'desember' => 'december',
        ];
    }

    private static function nl()
    {
        return [
            'januari' => 'january',
            'februari' => 'february',
            'maart' => 'march',
            'april' => 'april',
            'mei' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'augustus' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'december' => 'december',
        ];
    }

    public static function get($locale)
    {
        $ret = null;

        switch($locale) {
            case 'it_IT':
                $ret = self::it();
                break;

            case 'en_EN':
                $ret = self::en();
                break;

            case 'de_DE':
                $ret = self::de();
                break;

            case 'fr_FR':
                $ret = self::fr();
                break;

            case 'nb_NO':
                $ret = self::nb();
                break;

            case 'nl_NL':
                $ret = self::nl();
                break;
        }

        return $ret;
    }
}

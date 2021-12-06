<?php

namespace App\View\Texts;

class Days
{
    private static function it()
    {
        return [
            'lunedì' => 'monday',
            'martedì' => 'tuesday',
            'mercoledì' => 'wednesday',
            'giovedì' => 'thursday',
            'venerdì' => 'friday',
            'sabato' => 'saturday',
            'domenica' => 'sunday',
        ];
    }

    private static function en()
    {
        return [
            'monday' => 'monday',
            'tuesday' => 'tuesday',
            'wednesday' => 'wednesday',
            'thursday' => 'thursday',
            'friday' => 'friday',
            'saturday' => 'saturday',
            'sunday' => 'sunday',
        ];
    }

    private static function de()
    {
        return [
            'montag' => 'monday',
            'dienstag' => 'tuesday',
            'mittwoch' => 'wednesday',
            'donnerstag' => 'thursday',
            'freitag' => 'friday',
            'samstag' => 'saturday',
            'sonntag' => 'sunday',
        ];
    }

    private static function fr()
    {
        return [
            'lundi' => 'monday',
            'mardi' => 'tuesday',
            'mercredi' => 'wednesday',
            'jeudi' => 'thursday',
            'vendredi' => 'friday',
            'samedi' => 'saturday',
            'dimanche' => 'sunday',
        ];
    }

    private static function nb()
    {
        return [
            'mandag' => 'monday',
            'tirsdag' => 'tuesday',
            'onsdag' => 'wednesday',
            'torsdag' => 'thursday',
            'fredag' => 'friday',
            'lørdag' => 'saturday',
            'søndag' => 'sunday',
        ];
    }

    private static function nl()
    {
        return [
            'maandag' => 'monday',
            'dinsdag' => 'tuesday',
            'woensdag' => 'wednesday',
            'donderdag' => 'thursday',
            'vrijdag' => 'friday',
            'zaterdag' => 'saturday',
            'zondag' => 'sunday',
        ];
    }

    public static function get($locale)
    {
        switch($locale) {
            case 'it_IT':
                return self::it();
            case 'en_EN':
                return self::en();
            case 'de_DE':
                return self::de();
            case 'fr_FR':
                return self::fr();
            case 'nb_NO':
                return self::nb();
            case 'nl_NL':
                return self::nl();
        }
    }
}

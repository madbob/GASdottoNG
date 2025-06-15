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
        $ret = null;

        switch ($locale) {
            case 'it':
                $ret = self::it();
                break;

            case 'en':
                $ret = self::en();
                break;

            case 'de':
                $ret = self::de();
                break;

            case 'fr':
                $ret = self::fr();
                break;

            case 'nb':
                $ret = self::nb();
                break;

            case 'nl':
                $ret = self::nl();
                break;
        }

        return $ret;
    }
}

<?php

namespace app\Utils;

class Utils
{
    public static function decodeDate($date)
    {
        if ($date == '') {
            return '';
        }

        $months = [
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

        list($weekday, $day, $month, $year) = explode(' ', $date);
        $month = strtolower($month);
        if (!in_array($month, array_values($months))) {
            $month = $months[strtolower($month)];
        }
        $en_date = sprintf('%s %s %s', $day, $month, $year);
        return date('Y-m-d', strtotime($en_date));
    }
}

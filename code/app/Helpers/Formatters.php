<?php

function printablePrice($price)
{
    return sprintf('%.02f', $price);
}

function normalizePercentage($value)
{
    return str_replace(' ', '', $value);
}

function applyPercentage($original, $percentage)
{
    if (empty($percentage)) {
        return $original;
    }

    if (strpos($percentage, '%') !== false) {
        $p = (float)$percentage;

        return $original - (($original * $p) / 100);
    } else {
        return $original - $value;
    }
}

function decodeDate($date)
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

function http_csv_headers($filename)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

<?php

function printablePrice($price, $separator = '.')
{
    $ret = sprintf('%.02f', $price);
    if ($separator != '.')
        $ret = str_replace('.', $separator, $ret);

    return $ret;
}

function printableQuantity($quantity, $discrete, $decimals = 2, $separator = '.')
{
    if ($discrete)
        $ret = sprintf('%d', $quantity);
    else
        $ret = sprintf('%.0' . $decimals . 'f', $quantity);

    if ($separator != '.')
        $ret = str_replace('.', $separator, $ret);

    return $ret;
}

function normalizePercentage($value)
{
    if ($value == null)
        return '';
    else
        return str_replace(' ', '', $value);
}

function printablePercentage($value)
{
    if (empty($value))
        return printablePrice(0) . ' €';

    if (strpos($value, '%') !== false)
        return $value;
    else
        return printablePrice($value) . ' €';
}

function applyPercentage($original, $percentage)
{
    if (empty($percentage)) {
        return $original;
    }

    $p = (float)$percentage;
    $o = (float)$original;

    if (strpos($percentage, '%') !== false) {
        return $o - (($o * $p) / 100);
    }
    else {
        return $o - $p;
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

function decodeDateMonth($date)
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

    list($day, $month) = explode(' ', $date);
    $month = strtolower($month);
    if (!in_array($month, array_values($months))) {
        $month = $months[strtolower($month)];
    }

    $en_date = sprintf('%s %s %s', $day, $month, date('Y'));
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

function iban_split($iban, $field)
{
    switch($field) {
        case 'country':
            $start = 0;
            $length = 2;
            break;
        case 'check':
            $start = 2;
            $length = 2;
            break;
        case 'cin':
            $start = 4;
            $length = 1;
            break;
        case 'abi':
            $start = 5;
            $length = 5;
            break;
        case 'cab':
            $start = 10;
            $length = 5;
            break;
        case 'account':
            $start = 15;
            $length = 12;
            break;
    }

    $iban = str_replace(' ', '', strtoupper($iban));
    return substr($iban, $start, $length);
}

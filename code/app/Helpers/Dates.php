<?php

function printableDate($value, $short = false)
{
    if (is_null($value) || empty($value)) {
        return _i('Mai');
    }
    else {
        if (is_numeric($value)) {
            $t = $value;
        }
        else {
            $t = strtotime($value);
            if (empty($t)) {
                $t = $value;
            }
        }

        if ($short) {
            return ucwords(strftime('%d/%m/%Y', $t));
        }
        else {
            return ucwords(strftime('%A %d %B %Y', $t));
        }
    }
}

function readDate($date)
{
    if (preg_match('/\d{1,2}\/\d{1,2}\/\d{1,4}/', $date) == 1) {
        list($day, $month, $year) = explode('/', $date);
        if ($year < 1000)
            $year = (int)$year + 2000;

        return strtotime("$year-$month-$day");
    }

    if (preg_match('/\d{1,2}\.\d{1,2}\.\d{1,4}/', $date) == 1) {
        list($day, $month, $year) = explode('.', $date);
        if ($year < 1000)
            $year = (int)$year + 2000;

        return strtotime("$year-$month-$day");
    }

    return strtotime($date);
}

function periodicCycling()
{
    return [
        'all' => _i('Tutti'),
        'biweekly' => _i('Ogni due Settimane'),
        'month_first' => _i('Primo del Mese'),
        'month_second' => _i('Secondo del Mese'),
        'month_third' => _i('Terzo del Mese'),
        'month_fourth' => _i('Quarto del Mese'),
        'month_last' => _i('Ultimo del Mese'),
    ];
}

function printablePeriodic($value)
{
    if (empty($value))
        return '';

    $value_obj = json_decode($value);
    if (empty($value_obj)) {
        Log::error('Data periodica non riconosciuta: ' . $value);
        return '';
    }

    $day = '';
    $days = localeDays();
    foreach($days as $locale => $english) {
        if ($value_obj->day == $english) {
            $day = ucwords($locale);
            break;
        }
    }

    $cycles = periodicCycling();
    $cycle = $cycles[$value_obj->cycle];

    return sprintf('%s - %s - %s - %s', $day, $cycle, printableDate($value_obj->from), printableDate($value_obj->to));
}

function decodePeriodic($value)
{
    if (empty($value))
        return '';

    $values = explode(' - ', $value);
    if (count($values) < 4)
        return '';

    $ret = (object) [
        'day' => '',
        'cycle' => '',
        'from' => '',
        'to' => ''
    ];

    $day = strtolower($values[0]);
    $days = localeDays();
    $ret->day = $days[$day];

    $cycles = periodicCycling();
    foreach($cycles as $identifier => $string) {
        if ($values[1] == $string) {
            $ret->cycle = $identifier;
            break;
        }
    }

    $ret->from = decodeDate($values[2]);
    $ret->to = decodeDate($values[3]);

    return $ret;
}

function unrollPeriodic($value)
{
    if (!isset($value->from) || !isset($value->to)) {
        return [];
    }

    $start = new \DateTime($value->from);
    $start = $start->modify('-1 days');
    $end = new \DateTime($value->to);
    $end = $end->modify('+1 days');

    $days = [];

    if ($value->cycle == 'biweekly') {
        while (strtolower($start->format('l')) != $value->day) {
            $start = $start->modify('+1 days');
        }

        $all_days = new \DatePeriod($start, new \DateInterval('P2W'), $end);
        foreach($all_days as $d) {
            $days[] = $d->format('Y-m-d');
        }
    }
    else {
        $all_days = new \DatePeriod($start, new \DateInterval('P1D'), $end);

        $validity_start = 1;
        $validity_end = 31;

        switch($value->cycle) {
            case 'all':
                break;
            case 'month_first':
                $validity_start = 1;
                $validity_end = 7;
                break;
            case 'month_second':
                $validity_start = 8;
                $validity_end = 14;
                break;
            case 'month_third':
                $validity_start = 15;
                $validity_end = 21;
                break;
            case 'month_fourth':
                $validity_start = 22;
                $validity_end = 28;
                break;
            case 'month_last':
                $validity_start = 25;
                $validity_end = 31;
                break;
            default:
                Log::error('Tipo ciclicità non identificato: ' . $value->cycle);
                break;
        }

        foreach($all_days as $d) {
            $d_day = $d->format('d');
            if ($d_day < $validity_start || $d_day > $validity_end) {
                continue;
            }

            if (strtolower($d->format('l')) == $value->day) {
                $days[] = $d->format('Y-m-d');
            }
        }
    }

    return $days;
}

function decodeDate($date)
{
    $date = trim($date);

    if ($date == '' || $date == _i('Mai')) {
        return null;
    }

    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) == 1) {
        return $date;
    }

    $months = localeMonths();

    $tokens = explode(' ', $date);
    if (count($tokens) != 4) {
        Log::error('Undecodable date: ' . $date);
        return null;
    }

    list($weekday, $day, $month, $year) = $tokens;
    $month = $months[strtolower($month)];
    $en_date = sprintf('%s %s %s', $day, $month, $year);
    return date('Y-m-d', strtotime($en_date));
}

function decodeDateMonth($date)
{
    if ($date == '') {
        return '';
    }

    $months = localeMonths();
    list($day, $month) = explode(' ', $date);
    $month = $months[strtolower($month)];
    $en_date = sprintf('%s %s %s', $day, $month, date('Y'));
    return date('Y-m-d', strtotime($en_date));
}

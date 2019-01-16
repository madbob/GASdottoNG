<?php

function printablePrice($price, $separator = '.')
{
    $ret = sprintf('%.02f', $price);
    if ($separator != '.')
        $ret = str_replace('.', $separator, $ret);

    return $ret;
}

function printablePriceCurrency($price, $separator = '.')
{
    return sprintf('%s %s', printablePrice($price), currentAbsoluteGas()->currency);
}

function printableDate($value)
{
    if (is_null($value)) {
        return _i('Mai');
    }
    else {
        $t = strtotime($value);
        return ucwords(strftime('%A %d %B %G', $t));
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
    if (is_null($value))
        return '';
    else
        return str_replace(' ', '', $value);
}

function printablePercentage($value)
{
    if (empty($value))
        return printablePriceCurrency(0);

    if (strpos($value, '%') !== false)
        return $value;
    else
        return printablePriceCurrency($value);
}

function readPercentage($value)
{
    if (empty($value))
        return [printablePrice(0), false];

    if (strpos($value, '%') !== false)
        return [(float) $value, true];
    else
        return [printablePrice($value), false];
}

function savingPercentage($request, $name)
{
    /*
        Questa funzione è costruita in funzione di percentagefield.blade.php,
        che prevede un campo radio nominato 'percentage_type' con cui l'utente
        specifica se il valore immesso debba essere interpretato come valore
        assoluto o come percentuale
    */
    $value = $request->input($name);
    $is_percentage = $request->input($name . '_percentage_type', 'euro');
    if ($is_percentage == 'percentage')
        return $value . '%';
    else
        return $value;
}

function applyPercentage($original, $percentage, $op = '-')
{
    if (empty($percentage)) {
        return $original;
    }

    $p = (float)$percentage;
    $o = (float)$original;

    if (strpos($percentage, '%') !== false) {
        if ($op == '-')
            return $o - (($o * $p) / 100);
        else if ($op == '+')
            return $o + (($o * $p) / 100);
        else if ($op == '=')
            return ($o * $p) / 100;
    }
    else {
        if ($op == '-')
            return $o - $p;
        else if ($op == '+')
            return $o + $p;
        else if ($op == '=')
            return $p;
    }
}

function enforceNumber($value)
{
    if (is_numeric($value))
        return $value;
    else
        return 0;
}

function sanitizeId($identifier)
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '-', $identifier);
}

function sanitizeFilename($filename)
{
    return preg_replace('/[^0-9a-zA-Z \.]/', '-', $filename);
}

function normalizeUrl($url)
{
    $url = strtolower($url);
    if (starts_with($url, 'http') == false)
        $url = 'http://' . $url;

    if (filter_var($url, FILTER_VALIDATE_URL))
        return $url;
    else
        return false;
}

function decodeDate($date)
{
    if ($date == '' || $date == _i('Mai')) {
        return null;
    }

    $months = localeMonths();
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

    $months = localeMonths();
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

/*
    Se $format_callback è null, si assume che $contents sia una stringa da
    scrivere direttamente nel file CSV
*/
function output_csv($filename, $head, $contents, $format_callback, $out_file = null)
{
    $callback = function() use ($head, $contents, $format_callback, $out_file) {
        if (is_null($out_file))
            $FH = fopen('php://output', 'w');
        else
            $FH = fopen($out_file, 'w');

        if (is_null($format_callback)) {
            if (is_string($contents)) {
                fwrite($FH, $contents);
            }
            else if (is_array($contents)) {
                foreach ($contents as $c) {
                    fputcsv($FH, $c);
                }
            }
        }
        else {
            fputcsv($FH, $head);

            foreach ($contents as $c) {
                $row = $format_callback($c);
                fputcsv($FH, $row);
            }
        }

        fclose($FH);
    };

    if (is_null($out_file)) {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . str_replace(' ', '\\', $filename),
            'Expires' => '0',
            'Pragma' => 'public'
        ];

        return Response::stream($callback, 200, $headers);
    }
    else {
        $callback();
        return $out_file;
    }
}

function htmlize($string)
{
    $string = str_replace('"', '\"', $string);

    /*
        https://stackoverflow.com/questions/1960461/convert-plain-text-urls-into-html-hyperlinks-in-php
    */
    $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
    $string = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $string);

    $string = nl2br($string);

    return $string;
}

function as_selectable($array, $value_callback, $label_callback)
{
    $ret = [];

    foreach($array as $i => $a) {
        $ret[] = [
            'value' => $value_callback($i, $a),
            'label' => $label_callback($i, $a),
        ];
    }

    return $ret;
}

function as_choosable($array, $value_callback, $name_callback, $check_callback)
{
    $ret = [];

    foreach($array as $i => $a) {
        $value = $value_callback($i, $a);
        $ret[$value] = (object) [
            'name' => $name_callback($i, $a),
            'checked' => $check_callback($i, $a)
        ];
    }

    return $ret;
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

function humanSizeToBytes($size)
{
    $suffix = strtoupper(substr($size, -1));
    if (!in_array($suffix, array('P','T','G','M','K'))) {
        return (int)$size;
    }

    $val = substr($size, 0, -1);

    switch ($suffix) {
        case 'P':
            $val *= 1024;
        case 'T':
            $val *= 1024;
        case 'G':
            $val *= 1024;
        case 'M':
            $val *= 1024;
        case 'K':
            $val *= 1024;
            break;
    }

    return (int)$val;
}

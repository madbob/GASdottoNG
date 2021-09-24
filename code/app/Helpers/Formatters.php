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

        return ucwords(strftime('%A %d %B %Y', $t));
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

function isPercentage($value)
{
    return (strpos($value, '%') !== false);
}

function printablePercentage($value)
{
    if (empty($value))
        return printablePriceCurrency(0);

    if (isPercentage($value))
        return $value;
    else
        return printablePriceCurrency($value);
}

function readPercentage($value)
{
    if (empty($value))
        return [printablePrice(0), false];

    if (isPercentage($value))
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
    if (is_array($request)) {
        $value = $request[$name] ?? 0;
        $is_percentage = $request[$name . '_percentage_type'] ?? 'euro';
    }
    else {
        $value = $request->input($name);
        $is_percentage = $request->input($name . '_percentage_type', 'euro');
    }

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

    if (isPercentage($percentage)) {
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

function download_headers($mimetype, $filename)
{
    app('debugbar')->disable();

    header('Content-Type: ' . $mimetype);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

function http_csv_headers($filename)
{
    download_headers('text/csv', $filename);
}

function output_csv($filename, $head, $contents, $format_callback, $out_file = null)
{
    $callback = function() use ($head, $contents, $format_callback, $out_file) {
        if (is_null($out_file))
            $FH = fopen('php://output', 'w');
        else
            $FH = fopen($out_file, 'w');

        if (is_null($format_callback)) {
            if ($head) {
                fputcsv($FH, $head);
            }

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

function enablePdfPagesNumbers($pdf)
{
    $dompdf = $pdf->getDomPDF();
    $font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
    $dompdf->get_canvas()->page_text(34, 18, "{PAGE_NUM} / {PAGE_COUNT}", $font, 10, array(0, 0, 0));
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
        default:
            Log::error('Campo non gestito per IBAN: ' . $field);
            $start = 0;
            $length = 0;
            break;
    }

    $iban = str_replace(' ', '', strtoupper($iban));
    return substr($iban, $start, $length);
}

function humanSizeToBytes($size)
{
    $suffix = strtoupper(substr($size, -1));
    if (!in_array($suffix, array('P', 'T', 'G', 'M', 'K'))) {
        return (int) $size;
    }

    $val = (float) substr($size, 0, -1);

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

function normalizeAddress($street, $city, $cap)
{
    $street = str_replace(',', '', trim($street));
    $city = str_replace(',', '', trim($city));
    $cap = str_replace(',', '', trim($cap));
    return sprintf('%s, %s, %s', $street, $city, $cap);
}

/*
    Questo serve a separare le colonne per utenti e prodotti quando si generano
    i Dettagli Consegne che contengono tutto
*/
function splitFields($fields)
{
    $formattable_user = App\User::formattableColumns();
    $formattable_product = App\Order::formattableColumns('shipping');

    $ret = (object) [
        'headers' => [],
        'user_columns' => [],
        'product_columns' => [],
        'user_columns_names' => [],
        'product_columns_names' => [],
    ];

    foreach($fields as $f) {
        if (isset($formattable_user[$f])) {
            $ret->user_columns[] = $f;
            $ret->user_columns_names[] = $formattable_user[$f]->name;
            $ret->headers[] = $formattable_user[$f]->name;
        }
        else {
            $ret->product_columns[] = $f;
            $ret->product_columns_names[] = $formattable_product[$f]->name;
            $ret->headers[] = $formattable_product[$f]->name;
        }
    }

    return $ret;
}

function ue($value)
{
    return new \Illuminate\Support\HtmlString($value);
}

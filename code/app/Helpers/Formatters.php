<?php

function printablePrice($price, $separator = '.')
{
    $ret = sprintf('%.02f', $price);
    if ($separator != '.')
        $ret = str_replace('.', $separator, $ret);

    return $ret;
}

function defaultCurrency()
{
    static $currency = null;

    if (is_null($currency)) {
        $currency = App\Currency::where('context', 'default')->first();
    }

    return $currency;
}

function printablePriceCurrency($price, $separator = '.', $currency = null)
{
    if (is_null($currency)) {
        $currency = defaultCurrency();
    }

    return sprintf('%s %s', printablePrice($price), $currency->symbol);
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

function prettyFormatHtmlText($str)
{
    $url_pattern = '/(http|https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
    $str = preg_replace($url_pattern, '<a href="$0" target="_blank">$0</a>', $str);
    return nl2br($str);
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
            if ($head) {
                fputcsv($FH, $head);
            }

            foreach ($contents as $c) {
                $row = $format_callback($c);
                if ($row) {
                    fputcsv($FH, $row);
                }
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
    $formattable_user = App\Formatters\User::formattableColumns();
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

function usernamePattern()
{
    return '[A-Za-z0-9_@.\- ]{1,50}';
}

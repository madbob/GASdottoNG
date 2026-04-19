<?php

use Illuminate\Support\HtmlString;

function printableQuantity($quantity, $discrete, $decimals = 2, $separator = '.')
{
    if ($discrete) {
        $ret = sprintf('%d', $quantity);
    }
    else {
        $ret = sprintf('%.0' . $decimals . 'f', $quantity);
    }

    if ($separator != '.') {
        $ret = str_replace('.', $separator, $ret);
    }

    return $ret;
}

function enforceNumber($value)
{
    if (is_numeric($value)) {
        return $value;
    }
    else {
        return 0;
    }
}

function sanitizeId($identifier)
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '-', $identifier);
}

function sanitizeFilename($filename)
{
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    return preg_replace('/[^0-9a-zA-Z \.]/', '-', $filename);
}

function normalizeUrl($url)
{
    $url = strtolower($url);
    if (str_starts_with($url, 'http') === false) {
        $url = 'http://' . $url;
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    else {
        return false;
    }
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
    $callback = function () use ($head, $contents, $format_callback, $out_file) {
        $csv_separator = currentAbsoluteGas()->getConfig('csv_separator');

        if (is_null($out_file)) {
            $fh = fopen('php://output', 'w');
        }
        else {
            $fh = fopen($out_file, 'w');
        }

        if ($head) {
            fputcsv($fh, $head, $csv_separator);
        }

        if (is_null($format_callback)) {
            if (is_string($contents)) {
                fwrite($fh, $contents);
            }
            elseif (is_array($contents)) {
                foreach ($contents as $c) {
                    fputcsv($fh, $c, $csv_separator);
                }
            }
        }
        else {
            foreach ($contents as $c) {
                $row = $format_callback($c);
                if ($row) {
                    fputcsv($fh, $row, $csv_separator);
                }
            }
        }

        fflush($fh);
        $test = fclose($fh);
        if ($test == false) {
            \Log::warning('Errore chiudendo il file formattato in CSV');
        }
    };

    if (is_null($out_file)) {
        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . str_replace(' ', '\\', $filename),
            'Expires' => '0',
            'Pragma' => 'public',
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
    /*
        La funzione page_text() nel canvas di DomPDF viene eseguita su ogni
        pagina, ma prima devono esistere le pagine... Qui forzo un render()
        preventivo del PDF, in modo da fargli calcolare le tabelle
        (eventualmente suddivise in più pagine), dopodiché applico la funzione
        per la numerazione delle pagine
    */
    $pdf->render();

    $dompdf = $pdf->getDomPDF();
    $font = $dompdf->getFontMetrics()->get_font('helvetica', 'bold');
    $dompdf->get_canvas()->page_text(34, 18, '{PAGE_NUM} / {PAGE_COUNT}', $font, 10, [0, 0, 0]);
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
    switch ($field) {
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
    https://stackoverflow.com/a/65718002/3135371
*/
function closestNumber(array $array, $goal)
{
    return array_reduce($array, function ($carry, $item) use ($goal) {
        return abs($item - $goal) < abs($carry - $goal) ? $item : $carry;
    }, reset($array));
}

/*
    Aggregando molti ordini insieme, alcune composizioni grafiche nella
    visualizzazione degli aggregati diventano sostanzialmente illeggibili.
    Questa funzione ritorna un numero ragionevole di ordini entro cui si
    possono comporre stringhe e contenuti, superato il quale è consigliato
    adottare un'altra strategia
*/
function aggregatesConvenienceLimit()
{
    return 3;
}

function longCommentLimit()
{
    return 100;
}

function ue($value)
{
    return new HtmlString($value);
}

function usernamePattern()
{
    return '[A-Za-z0-9_@.\- ]{1,50}';
}

function formatAccordionLabel($label, $icon)
{
    $text = __('texts.' . $label);
    return sprintf('<i class="bi-%s"></i> %s', $icon, $text);
}

function baseEncrypt($string)
{
    $key = substr(config('app.key'), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($string, $nonce, $key));
}

function baseDecrypt($string)
{
    $key = substr(config('app.key'), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    $decoded = base64_decode($string);
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
    return sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
}

function publicGateGetLink($type, $action = 'show', $params = [])
{
    $payload = [
        't' => $type,
        'p' => $params,
    ];

    $string = json_encode($payload);
    $encrypted = baseEncrypt($string);

    return route('public.gate', [
        'token' => $encrypted,
        'action' => $action,
    ]);
}

function publicGateRetrieveLink($encrypted)
{
    $string = baseDecrypt($encrypted);
    return json_decode($string);
}

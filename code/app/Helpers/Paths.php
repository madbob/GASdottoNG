<?php

/*
    Se la variabile d'ambiente STORAGE_FOLDER è definita, tutti i files vengono
    conservati nella cartella (contenuta in storage/) con quel nome.
    Utile per isolare i files in configurazioni multi-GAS
*/
function gas_storage_path($path = null, $folder = false)
{
    $ret = storage_path();

    $local = env('STORAGE_FOLDER', null);
    if ($local != null) {
        $ret .= sprintf('/%s', $local);
    }

    if ($path != null) {
        $ret .= sprintf('/%s', $path);
    }

    if ($folder) {
        if (file_exists($ret) == false) {
            mkdir($ret, 0777);
        }
    }

    return $ret;
}

function env_file()
{
    if (global_multi_installation()) {
        if (isset($_SERVER['HTTP_HOST'])) {
            $instance = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        }
        else {
            /*
                Quando eseguo i comandi in cron sulle istanze di gasdotto.net
                non ho nessun parametro $_SERVER['HTTP_HOST'] di riferimento.
                Pertanto desumo il corretto file .env da cui attingere in
                funzione dell'URL definito nella configurazione
            */
            $domain = parse_url(env('APP_URL'), PHP_URL_HOST);
            $instance = preg_replace('/^([^\.]*)\.gasdotto\.net.*$/', '\1', $domain);
        }

        return '.env.' . $instance;
    }
    else {
        return '.env';
    }
}

function fixUrl($url)
{
    if (Illuminate\Support\Str::startsWith($url, 'http') == false) {
        $url = 'http://' . $url;
    }

    return $url;
}

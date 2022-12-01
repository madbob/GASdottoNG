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
    if (global_multi_installation() && isset($_SERVER['HTTP_HOST'])) {
        $instance = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        return ('.env.' . $instance);
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

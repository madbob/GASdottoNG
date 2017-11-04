<?php

/*
    Se la variabile d'ambiente STORAGE_FOLDER è definita, tutti i files vengono
    conservati nella cartella (contenuta in storage/) con quel nome.
    Utile per isolare i files in configurazioni multi-GAS
*/
function gas_storage_path($path = null)
{
    $ret = storage_path();

    $local = env('STORAGE_FOLDER', null);
    if ($local != null)
        $ret .= sprintf('/%s', $local);

    if ($path != null)
        $ret .= sprintf('/%s', $path);

    return $ret;
}

function env_file()
{
    if (global_multi_installation()) {
        $instance = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        return ('.env.' . $instance);
    }
    else {
        return '.env';
    }
}

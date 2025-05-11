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
        if (file_exists($ret) === false) {
            mkdir($ret, 0777);
        }
    }

    return $ret;
}

/*
    Questa funzione restituisce il file .env relativo all'istanza.-
    In caso di una installazione standalone non ci sono particolari problemi: il
    file .env è .env
    In caso di una installazione in multi-tenancy, il file dipende dal dominio
    HTTP che viene richiesto. Questo comportamento viene sfruttato in
    bootstrap/app.php per inizializzare l'istanza ad ogni richiesta HTTP.
    Se invece sto eseguendo uno script CLI (ad esempio: i comandi eseguiti in
    cron), e non posso accedere alla variabile $_SERVER (non esistendo alcuna
    richiesta HTTP da leggere), i possibili comportamenti sono due: o appunto ho
    appena eseguito il comando, dunque non è ancora stato inizializzato niente,
    dunque prendo per buono sempre e solo .env, altrimenti desumo l'istanza
    locale dal valore di APP_URL (che nel frattempo è stato letto dal file .env
    giusto)
*/
function env_file()
{
    if (global_multi_installation()) {
        $instance = null;

        if (isset($_SERVER['HTTP_HOST'])) {
            $instance = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
        }
        elseif (app()->runningInConsole()) {
            $domain = parse_url(env('APP_URL'), PHP_URL_HOST);
            $instance = preg_replace('/^([^\.]*)\.gasdotto\.net.*$/', '\1', $domain);
        }

        if ($instance) {
            return '.env.' . $instance;
        }
    }

    return '.env';
}

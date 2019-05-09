<?php

/*

Configurazione nginx di esempio per gestire istanze multiple nella stessa cartella

server {
    listen   80;

    server_name  ~^(?<instance>\w+)\.gasdotto\.net$;
    root   /var/www/gasdotto/ng/code/public;

    index  index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files      $uri /index.php =404;
        fastcgi_pass   unix:/run/php/php7.0-fpm.sock;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
}

*/

function global_multi_installation()
{
    return false;
}

function get_instances()
{
    $ret = [];

    $path = base_path('.env.*');
    $files = glob($path);

    foreach($files as $file) {
        $config = file($file);

        foreach($config as $c) {
            $c = trim($c);

            if (strncmp($c, 'DB_DATABASE', strlen('DB_DATABASE')) == 0) {
                list($useless, $dbname) = explode('=', $c);
                $ret[] = $dbname;
                break;
            }
        }
    }

    return $ret;
}

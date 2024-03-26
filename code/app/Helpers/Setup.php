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
    return true;
}

function read_instance_config_file($path)
{
    $config = file($path);
    $params = [];

    foreach($config as $c) {
        $c = trim($c);
        if (empty($c) == false) {
            if (strpos($c, '=') === false) {
                \Log::error('Configurazione ambigua in file ' . $path . ' - ' . $c);
            }
            else {
                list($name, $value) = explode('=', $c);
                $params[$name] = $value;
            }
        }
    }

    return $params;
}

function get_instances()
{
    $ret = [];

    $path = base_path('.env.*');
    $files = glob($path);

    foreach($files as $file) {
        $params = read_instance_config_file($file);
        if (isset($params['DB_DATABASE'])) {
            $ret[] = $params['DB_DATABASE'];
        }
    }

    return $ret;
}

function get_instance_db($name)
{
    $path = base_path('.env.' . $name);
    $params = read_instance_config_file($path);

    $db_config = [
        'driver' => $params['DB_CONNECTION'],
        'host' => $params['DB_HOST'],
        'username' => $params['DB_USERNAME'],
        'password' => $params['DB_PASSWORD'],
        'database' => $params['DB_DATABASE'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'strict' => false,
    ];

    $factory = App::make('db.factory');
    return $factory->make($db_config);
}

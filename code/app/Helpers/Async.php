<?php

/*
    https://cwhite.me/blog/fire-and-forget-http-requests-in-php
*/
function async_job($action, $data)
{
    try {
        $endpoint = route('job.execute');

        $postData = (object) $data;
        $postData->action = $action;
        $postData->gas_id = App::make('GlobalScopeHub')->getGas();
        $postData->auth_key = substr(env('APP_KEY'), -5);
        $postData = json_encode($postData);

        $endpointParts = parse_url($endpoint);
        $endpointParts['path'] = $endpointParts['path'] ?? '/';
        $endpointParts['port'] = $endpointParts['port'] ?? $endpointParts['scheme'] === 'https' ? 443 : 80;

        $contentLength = strlen($postData);

        $request = "POST {$endpointParts['path']} HTTP/1.1\r\n";
        $request .= "Host: {$endpointParts['host']}\r\n";
        $request .= "Content-Length: {$contentLength}\r\n";
        $request .= "Content-Type: application/json\r\n\r\n";
        $request .= $postData;

        $prefix = substr($endpoint, 0, 8) === 'https://' ? 'tls://' : '';
        $socket = fsockopen($prefix . $endpointParts['host'], $endpointParts['port']);
        fwrite($socket, $request);
        fclose($socket);
    }
    catch(\Exception $e) {
        Log::error('Fallita invocazione funzione asincrona ' . $action . ': ' . $e->getMessage());
    }
}

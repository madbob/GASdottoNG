<?php

return [
    /*
        Array of middleware groups to handle the RSS requests
    */
    'middleware' => [],

    /*
        Prefix for the RSS fetching route.
        The final route will be
        $prefix . '/logs'
        But anyway it has the name "log2rss.index"
    */
    'prefix' => substr(env('APP_KEY'), 7, 10),

    /*
        Minimum log level for the items to include into the feed
    */
    'log_level' => 'warning',

    /*
        Limit of items to include into the feed
    */
    'limit' => 20,
];

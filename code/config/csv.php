<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Delimiter
    |--------------------------------------------------------------------------
    |
    | Delimiter option sets the field delimiter (one character only).
    |
    */

    'delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | Default Enclosure
    |--------------------------------------------------------------------------
    |
    | Enclosure option sets the field enclosure character (one character only).
    |
    */

    'enclosure' => '"',

    /*
    |--------------------------------------------------------------------------
    | Default Escape
    |--------------------------------------------------------------------------
    |
    | Escape option sets the escape character (one character only).
    |
    */

    'escape' => '\\',

    /*
    |--------------------------------------------------------------------------
    | Convert CSV File Encoding
    |--------------------------------------------------------------------------
    |
    | Options are set separately for reader and writer.
    |
    | enabled - if set to true enables encoding conversion
    | from - input encoding
    | to - output encoding
    |
    */

    'encoding' => [
        'reader' => [
            'enabled' => false,
            'from' => 'CP1250',
            'to' => 'UTF-8',
        ],
        'writer' => [
            'enabled' => false,
            'from' => 'UTF-8',
            'to' => 'CP1250',
        ],
    ],
];

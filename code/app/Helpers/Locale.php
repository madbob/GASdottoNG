<?php

function currentLang()
{
    static $lang = '';

    if (empty($lang)) {
        $lang = currentAbsoluteGas()->getConfig('language');
    }

    return $lang;
}

function htmlLang()
{
    return str_replace('_', '-', currentLang());
}

/*
    Se vengono aggiunte nuove traduzioni, correggere anche il file
    code/config/laravel-gettext.php
*/
function getLanguages()
{
    return [
        [
            'value' => 'it_IT',
            'label' => 'Italiano'
        ],
        [
            'value' => 'en_EN',
            'label' => 'English'
        ],
    ];
}

function localeMonths()
{
    $lang = currentLang();
    $months = [];

    switch($lang) {
        case 'it_IT':
            $months = [
                'gennaio' => 'january',
                'febbraio' => 'february',
                'marzo' => 'march',
                'aprile' => 'april',
                'maggio' => 'may',
                'giugno' => 'june',
                'luglio' => 'july',
                'agosto' => 'august',
                'settembre' => 'september',
                'ottobre' => 'october',
                'novembre' => 'november',
                'dicembre' => 'december',
            ];
            break;

        case 'en_EN':
            $months = [
                'january' => 'january',
                'february' => 'february',
                'march' => 'march',
                'april' => 'april',
                'may' => 'may',
                'june' => 'june',
                'july' => 'july',
                'august' => 'august',
                'september' => 'september',
                'october' => 'october',
                'november' => 'november',
                'december' => 'december',
            ];
            break;
    }

    return $months;
}

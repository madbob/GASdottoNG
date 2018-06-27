<?php

function currentLang()
{
    static $lang = '';

    if (empty($lang)) {
        /*
            Nel caso estremo in cui non ci sia alcun GAS recuperabile chiamando
            questa funzione, assumo che la lingua sia l'italiano. Ma non salvo
            questa informazione nella variabile statica, sperando che alla
            prossima iterazione possa accedere ad un GAS effettivo.
            Serve soprattutto a far funzionare gli unit test...
        */
        $gas = currentAbsoluteGas();
        if (is_null($gas))
            return 'it_IT';
        else
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
        [
            'value' => 'de_DE',
            'label' => 'Deutsch'
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

        case 'de_DE':
            $months = [
                'january' => 'januar',
                'february' => 'februar',
                'march' => 'märz',
                'april' => 'april',
                'may' => 'mai',
                'june' => 'juni',
                'july' => 'juli',
                'august' => 'august',
                'september' => 'september',
                'october' => 'oktober',
                'november' => 'november',
                'december' => 'dezember',
            ];
            break;
    }

    return $months;
}

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
        if (is_null($gas)) {
            return 'it_IT';
        }
        else {
            $lang = $gas->getConfig('language');
        }
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
        'it_IT' => 'Italiano',
        'en_EN' => 'English',
        'de_DE' => 'Deutsch',
        'fr_FR' => 'Français',
        'nl_NL' => 'Nederlands',
        'nb_NO' => 'Norwegian Bokmål',
    ];
}

function localeMonths()
{
    $lang = currentLang();
    return App\View\Texts\Months::get($lang);
}

function localeDays()
{
    $lang = currentLang();
    return App\View\Texts\Days::get($lang);
}

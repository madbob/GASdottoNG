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

function translateNumberFormat($value)
{
    $last_dot = strrpos($value, '.');
    $last_comma = strrpos($value, ',');

    if ($last_dot > $last_comma) {
        return (float) str_replace(',', '', $value);
    }
    else {
        $value = str_replace('.', '', $value);

        return (float) strtr($value, ',', '.');
    }
}

function guessDecimal($value)
{
    $has_dot = (strpos($value, '.') !== false);
    $has_comma = (strpos($value, ',') !== false);

    if ($has_dot === false && $has_comma === false) {
        return (int) $value;
    }

    if ($has_dot && $has_comma === false) {
        return (float) $value;
    }

    // @phpstan-ignore-next-line
    if ($has_dot === false && $has_comma) {
        return (float) strtr($value, ',', '.');
    }

    return translateNumberFormat($value);
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

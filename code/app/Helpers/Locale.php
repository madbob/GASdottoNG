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
            return 'it';
        }
        else {
            $lang = $gas->getConfig('language');
        }
    }

    return explode('_', $lang)[0];
}

function currentLangExtended()
{
    $extended = [
        'it' => 'it_IT',
        'en' => 'en_EN',
        'de' => 'de_DE',
        'fr' => 'fr_FR',
        'nl' => 'nl_NL',
        'nb' => 'nb_NO',
    ];

    $lang = currentLang();
    return $extended[$lang] ?? 'it_IT';
}

function htmlLang()
{
    return str_replace('_', '-', currentLangExtended());
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
        $ret = (int) $value;
    }
    elseif ($has_dot && $has_comma === false) {
        $ret = (float) $value;
    }
    elseif ($has_dot === false && $has_comma) {
        $ret = (float) strtr($value, ',', '.');
    }
    else {
        $ret = translateNumberFormat($value);
    }

    return $ret;
}

function getLanguages()
{
    return [
        'it' => 'Italiano',
        'en' => 'English',
        'de' => 'Deutsch',
        'fr' => 'Français',
        'nl' => 'Nederlands',
        'nb' => 'Norwegian Bokmål',
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

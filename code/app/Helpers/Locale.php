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

function serializeClientTranslations()
{
    $ret = [
        'texts.orders.help.booking_already_exists' => __('texts.orders.help.booking_already_exists'),
        'texts.orders.help.void_booking' => __('texts.orders.help.void_booking'),
        'texts.orders.help.shipping_zero' => __('texts.orders.help.shipping_zero'),
        'texts.generic.delete_confirmation' => __('texts.generic.delete_confirmation'),
        'texts.generic.saved' => __('texts.generic.saved'),
        'texts.orders.help.booked_disabled_product' => __('texts.orders.help.booked_disabled_product'),
        'texts.generic.send_mail' => __('texts.generic.send_mail'),
        'texts.generic.save' => __('texts.generic.save'),
        'texts.auth.password' => __('texts.auth.password'),
        'texts.auth.confirm_password' => __('texts.auth.confirm_password'),
        'texts.auth.enforce_change' => __('texts.auth.enforce_change'),
        'texts.generic.cancel' => __('texts.generic.cancel'),
        'texts.generic.confirm' => __('texts.generic.confirm'),
        'texts.auth.wrong' => __('texts.auth.wrong'),
        'texts.user.address_elements.street' => __('texts.user.address_elements.street'),
        'texts.user.address_elements.city' => __('texts.user.address_elements.city'),
        'texts.user.address_elements.zip' => __('texts.user.address_elements.zip'),
        'texts.generic.day' => __('texts.generic.day'),
        'texts.generic.days.monday' => __('texts.generic.days.monday'),
        'texts.generic.days.tuesday' => __('texts.generic.days.tuesday'),
        'texts.generic.days.wednesday' => __('texts.generic.days.wednesday'),
        'texts.generic.days.thursday' => __('texts.generic.days.thursday'),
        'texts.generic.days.friday' => __('texts.generic.days.friday'),
        'texts.generic.days.saturday' => __('texts.generic.days.saturday'),
        'texts.generic.days.sunday' => __('texts.generic.days.sunday'),
        'texts.notifications.cycle_param' => __('texts.notifications.cycle_param'),
        'texts.generic.all' => __('texts.generic.all'),
        'texts.generic.export' => __('texts.generic.export'),
        'texts.notifications.cycle.two_weeks' => __('texts.notifications.cycle.two_weeks'),
        'texts.notifications.cycle.first_of_month' => __('texts.notifications.cycle.first_of_month'),
        'texts.notifications.cycle.second_of_month' => __('texts.notifications.cycle.second_of_month'),
        'texts.notifications.cycle.third_of_month' => __('texts.notifications.cycle.third_of_month'),
        'texts.notifications.cycle.fourth_of_month' => __('texts.notifications.cycle.fourth_of_month'),
        'texts.notifications.cycle.last_of_month' => __('texts.notifications.cycle.last_of_month'),
        'texts.generic.since' => __('texts.generic.since'),
        'texts.generic.to' => __('texts.generic.to'),
        'texts.permissions.revoke_confirm' => __('texts.permissions.revoke_confirm'),
        'texts.generic.error' => __('texts.generic.error'),
        'texts.generic.attachments.large_file' => __('texts.generic.attachments.large_file'),
    ];

    return $ret;
}

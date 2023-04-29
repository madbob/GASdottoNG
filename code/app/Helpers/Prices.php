<?php

function printablePrice($price, $separator = '.')
{
    $ret = sprintf('%.02f', $price);
    if ($separator != '.')
        $ret = str_replace('.', $separator, $ret);

    return $ret;
}

function defaultCurrency()
{
    static $currency = null;

    if (is_null($currency)) {
        $currency = App\Currency::where('context', 'default')->first();
    }

    return $currency;
}

function printablePriceCurrency($price, $separator = '.', $currency = null)
{
    if (is_null($currency)) {
        $currency = defaultCurrency();
    }

    return sprintf('%s %s', printablePrice($price), $currency->symbol);
}

function priceDiffTolerance($first, $second)
{
    if (abs(round($first - $second, 2)) <= 0.01) {
        return $first;
    }
    else {
        return $second;
    }
}

<?php

function normalizePercentage($value)
{
    if (is_null($value))
        return '';
    else
        return str_replace(' ', '', $value);
}

function isPercentage($value)
{
    return (strpos($value, '%') !== false);
}

function printablePercentage($value)
{
    if (empty($value))
        return printablePriceCurrency(0);

    if (isPercentage($value))
        return $value;
    else
        return printablePriceCurrency($value);
}

function readPercentage($value)
{
    if (empty($value))
        return [printablePrice(0), false];

    if (isPercentage($value))
        return [(float) $value, true];
    else
        return [printablePrice($value), false];
}

function savingPercentage($request, $name)
{
    /*
        Questa funzione è costruita in funzione di percentagefield.blade.php,
        che prevede un campo radio nominato 'percentage_type' con cui l'utente
        specifica se il valore immesso debba essere interpretato come valore
        assoluto o come percentuale
    */
    if (is_array($request)) {
        $value = $request[$name] ?? 0;
        $is_percentage = $request[$name . '_percentage_type'] ?? 'euro';
    }
    else {
        $value = $request->input($name);
        $is_percentage = $request->input($name . '_percentage_type', 'euro');
    }

    if ($is_percentage == 'percentage')
        return $value . '%';
    else
        return $value;
}

function applyPercentage($original, $percentage, $op = '-')
{
    if (empty($percentage)) {
        return $original;
    }

    $p = (float)$percentage;
    $o = (float)$original;

    if (isPercentage($percentage)) {
        if ($op == '-')
            return $o - (($o * $p) / 100);
        else if ($op == '+')
            return $o + (($o * $p) / 100);
        else if ($op == '=')
            return ($o * $p) / 100;
    }
    else {
        if ($op == '-')
            return $o - $p;
        else if ($op == '+')
            return $o + $p;
        else if ($op == '=')
            return $p;
    }
}

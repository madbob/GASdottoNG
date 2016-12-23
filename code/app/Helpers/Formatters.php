<?php

function printablePrice($price)
{
    return sprintf('%.02f', $price);
}

function normalizePercentage($value)
{
    return str_replace(' ', '', $value);
}

function applyPercentage($original, $percentage)
{
    if (empty($percentage)) {
        return $original;
    }

    if (strpos($percentage, '%') !== false) {
        $p = (float) $percentage;

        return $original - (($original * $p) / 100);
    } else {
        return $original - $value;
    }
}

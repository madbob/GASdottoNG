<?php

function isPercentage($value)
{
    return (strpos($value, '%') !== false);
}

function formatPercentage($value, $percentage)
{
    if ($percentage) {
        return sprintf('%s%%', $value);
    }
    else {
        return (string) $value;
    }
}

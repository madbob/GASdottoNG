<?php

function htmlLang()
{
    return str_replace('_', '-', LaravelGettext::getLocale());
}

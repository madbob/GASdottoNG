<?php

namespace App\Importers\GDXP;

abstract class GDXPImporter
{
    protected static function xmlDateFormat($string)
    {
        $d = html_entity_decode($string);
        $year = substr($d, 0, 4);
        $month = substr($d, 4, 2);
        $day = substr($d, 6, 2);
        return sprintf('%d-%d-%d', $year, $month, $day);
    }

    abstract public static function readXML($xml);
    abstract public static function readJSON($json);
}

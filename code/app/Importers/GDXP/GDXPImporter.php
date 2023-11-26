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

    protected static function handleTransformations($parent, $json)
    {
        if (isset($json->transformations)) {
            foreach($json->transformations as $json_transformation) {
                Transformations::importJSON($parent, $json_transformation);
            }
        }
    }

    protected static function handleAttachments($parent, $json)
    {
        if (isset($json->attachments)) {
            foreach($json->attachments as $json_attachment) {
                Attachments::importJSON($parent, $json_attachment);
            }
        }
    }

    abstract public static function readXML($xml);
    abstract public static function readJSON($json);
}

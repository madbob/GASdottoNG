<?php

/*
    I Formatters permettono di serializzare diverse tipologie di oggetti in
    semplici array, selettivamente accedendo agli attributi desiderati.
    Utile per formattare poi documenti esportati (PDF e CSV) o tabelle HTML.
*/

namespace App\Formatters;

abstract class Formatter
{
    public static function getHeaders($fields)
    {
        $columns = static::formattableColumns();
        $headers = [];

        foreach($fields as $field) {
            $headers[] = $columns[$field]->name;
        }

        return $headers;
    }

    public static function format($obj, $fields, $context = null)
    {
        $columns = static::formattableColumns();
        $ret = [];

        foreach($fields as $f) {
            try {
                $format = $columns[$f]->format ?? null;

                if ($format) {
                    $ret[] = call_user_func($format, $obj, $context);
                }
                else {
                    $ret[] = accessAttr($obj, $f);
                }
            }
            catch(\Exception $e) {
                Log::error('Formattazione: impossibile accedere al campo ' . $f . ' di ' . $obj->id);
                $ret[] = '';
            }
        }

        return $ret;
    }

    public static abstract function formattableColumns($type = null);
}

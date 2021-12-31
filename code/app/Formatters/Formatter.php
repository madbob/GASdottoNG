<?php

/*
    I Formatters permettono di serializzare diverse tipologie di oggetti in
    semplici array, selettivamente accedendo agli attributi desiderati.
    Utile per formattare poi documenti esportati (PDF e CSV) o tabelle HTML.
*/

namespace App\Formatters;

use Log;

abstract class Formatter
{
    public static function getHeaders($fields)
    {
        $columns = static::formattableColumns('all');
        $headers = [];

        foreach($fields as $field) {
            $headers[] = $columns[$field]->name;
        }

        return $headers;
    }

    public static function format($obj, $fields, $context = null)
    {
        $columns = static::formattableColumns('all');
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
                Log::error('Formattazione: impossibile accedere al campo ' . $f . ' di ' . $obj->id . ': ' . $e->getMessage());
                $ret[] = '';
            }
        }

        return $ret;
    }

    public static function formatArray($objs, $fields, $context = null)
    {
        $ret = [];

        foreach($objs as $obj) {
            $children = static::children($obj);
            if ($children) {
                $child_formatter = $children->formatter;

                foreach($children->children as $child) {
                    $rows = $child_formatter::format($child, $fields, $context);
                    $ret = array_merge($ret, [$rows]);
                }
            }
            else {
                $rows = self::format($obj, $fields, $context);
                $ret = array_merge($ret, [$rows]);
            }
        }

        return $ret;
    }

    protected static function children($obj)
    {
        return false;
    }

    /*
        $type può essere una qualsiasi stringa che identifica il contesto, e
        pertanto le colonne desiderate in quel contesto.
        Ma se viene specificato $type = 'all' tutte le colonne di tutti i tipi
        devono essere restituite (in getHeaders() e format() vengono prese tutte
        le colonne possibili e si formattano solo quelle espressamente richieste
        in quel momento)
    */
    public static abstract function formattableColumns($type = null);
}

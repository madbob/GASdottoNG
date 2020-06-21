<?php

function currentAbsoluteGas()
{
    static $gas = null;

    if (is_null($gas)) {
        $user = Auth::user();
        if ($user != null)
            $gas = $user->gas;
        else
            $gas = App\Gas::orderBy('created_at', 'asc')->first();
    }

    return $gas;
}

function modelsUsingTrait($trait_name) {
    $out = [];
    $results = scandir(app_path());

    foreach ($results as $result) {
        if ($result === '.' or $result === '..')
            continue;

        if (is_dir(app_path() . '/' . $result))
            continue;

        $classname = 'App\\' . substr($result, 0, -4);
        if (class_exists($classname) && in_array($trait_name, class_uses($classname)))
            $out[$classname] = $classname::commonClassName();
    }

    return $out;
}

function accessAttr($obj, $name, $default = '')
{
    if (is_null($obj))
        return $default;

    if (strpos($name, '->') !== false) {
        list($array, $index) = explode('->', $name);
        if (isset($obj->$array[$index]))
            return $obj->$array[$index];
        else
            return '';
    }
    else {
        return $obj->$name;
    }
}

function normalizeId($subject)
{
    if (is_object($subject)) {
        return $subject->id;
    }
    else {
        return $subject;
    }
}

/*
    Questi sono gli attributi che descrivono estensivamente una prenotazione
    (modificatori a parte, che sono gestiti in altra sede)
*/
function describingAttributes()
{
    return [
        'price',
        'weight',
        'quantity',
        'quantity_pieces',

        'price_delivered',
        'weight_delivered',
        'delivered',
        'delivered_pieces',
    ];
}

function describingAttributesMerge($first, $second, $sum = true)
{
    foreach(describingAttributes() as $attr) {
        if (!isset($first->$attr)) {
            $first->$attr = 0;
        }

        if (!isset($second->attr)) {
            continue;
        }

        if ($sum) {
            $first->$attr += $second->$attr;
        }
        else {
            $first->$attr -= $second->$attr;
        }
    }

    return $first;
}

<?php

function currentAbsoluteGas()
{
    static $gas = null;

    if ($gas == null) {
        $user = Auth::user();
        if ($user != null)
            $gas = $user->gas;
        else
            $gas = App\Gas::first();
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
        if (in_array($trait_name, class_uses($classname)))
            $out[$classname] = $classname::commonClassName();
    }

    return $out;
}

function accessAttr($obj, $name, $default = '')
{
    if ($obj == null)
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

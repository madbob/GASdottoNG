<?php

/*
    Questa funzione deve sempre tornare un GAS: quello impostato nel
    GlobalScopeHub, quello dell'utente corrente, o alla peggio il primo che si
    trova nel database
*/
function currentAbsoluteGas()
{
    $gas = null;

    $hub = App::make('GlobalScopeHub');
    if ($hub->enabled()) {
        $gas = $hub->getGasObj();
    }

    if (is_null($gas)) {
        $user = Auth::user();

        if (is_null($user) == false) {
            $gas = $user->gas;
        }

        if (is_null($gas)) {
            $gas = App\Gas::orderBy('created_at', 'asc')->first();
        }
    }

    return $gas;
}

function classesInNamespace($namespace)
{
    return HaydenPierce\ClassFinder\ClassFinder::getClassesInNamespace($namespace);
}

function systemParameters($type)
{
    static $types = [];

    if (!isset($types[$type])) {
        $types[$type] = [];
        $classes = classesInNamespace('App\\Parameters\\' . $type);

        foreach($classes as $class) {
            $rclass = new \ReflectionClass($class);
            if ($rclass->isInstantiable()) {
                $m = new $class();
                $types[$type][$m->identifier()] = $m;
            }
        }
    }

    return $types[$type];
}

function modelsUsingTrait($trait_name)
{
    $out = [];
    $results = scandir(app_path());

    foreach ($results as $result) {
        if ($result === '.' || $result === '..') {
            continue;
        }

        if (is_dir(app_path() . '/' . $result)) {
            continue;
        }

        $classname = 'App\\' . substr($result, 0, -4);
        if (class_exists($classname) && in_array($trait_name, class_uses($classname))) {
            $out[$classname] = $classname::commonClassName();
        }
    }

    return $out;
}

function hasTrait($obj, $trait)
{
    return in_array($trait, class_uses(get_class($obj)));
}

function accessAttr($obj, $name, $default = '')
{
    if (is_null($obj))
        return $default;

    if (strpos($name, '->') !== false) {
        list($array, $index) = explode('->', $name);
        if (isset($obj->$array[$index])) {
            return $obj->$array[$index];
        }
        else {
            return '';
        }
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

function inlineId($obj)
{
    $class = get_class($obj);
    list($namespace, $class) = explode('\\', $class);
    return sprintf('%s---%s', $class, $obj->id);
}

function fromInlineId($id)
{
    list($class, $id) = explode('---', $id);
    $class = sprintf('App\\%s', $class);
    return $class::find($id);
}

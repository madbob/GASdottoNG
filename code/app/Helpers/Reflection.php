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
    }

    if (is_null($gas)) {
        $gas = App\Gas::orderBy('created_at', 'asc')->first();
    }

    return $gas;
}

function classesInNamespace($namespace)
{
    return HaydenPierce\ClassFinder\ClassFinder::getClassesInNamespace($namespace);
}

/*
    Nota bene: quando si aggiunge una classe tra i parametri dinamici è
    opportuno eseguire il comando
    composer dumpautoload
    per caricare la nuova classe nell'autoload generato. Tutte le classi devono
    essere PSR-4 (il nome della classe deve coincidere col nome del file)
*/
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
    $results = array_diff(scandir(app_path()), ['.', '..']);

    foreach ($results as $result) {
        if (is_dir(app_path() . '/' . $result)) {
            continue;
        }

        $classname = 'App\\' . substr($result, 0, -4);
        if (class_exists($classname) && hasTrait($classname, $trait_name)) {
            $out[$classname] = $classname::commonClassName();
        }
    }

    return $out;
}

function hasTrait($obj, $trait)
{
    $traits = class_uses_recursive($obj);
    return in_array($trait, $traits);
}

function accessAttr($obj, $name, $default = '')
{
    if (is_null($obj))
        return $default;

    if (strpos($name, '->') !== false) {
        list($array, $index) = explode('->', $name);
        return $obj->$array[$index] ?? '';
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
    $tokens = explode('\\', $class);
    return sprintf('%s---%s', $tokens[1], $obj->id);
}

function fromInlineId($identifier)
{
    $parts = explode('---', $identifier);
    if (count($parts) != 2) {
        throw new \Exception("Identificativo non valido per recupero riferimento: " . $identifier, 1);
    }

    list($class, $id) = $parts;
    $class = sprintf('App\\%s', $class);

    $ret = $class::find($id);
    if (is_null($ret)) {
        \Log::error("Identificativo non valido per recupero riferimento: " . $identifier);
    }

    return $ret;
}

function unrollSpecialSelectors($users)
{
    $map = [];

    if (!is_array($users)) {
        return $map;
    }

    foreach ($users as $u) {
        if (strrpos($u, 'special::', -strlen($u)) !== false) {
            if (strrpos($u, 'special::role::', -strlen($u)) !== false) {
                $role_id = substr($u, strlen('special::role::'));
                $role = App\Role::find($role_id);
                foreach ($role->users as $u) {
                    $map[] = $u->id;
                }
            }
            elseif (strrpos($u, 'special::order::', -strlen($u)) !== false) {
                $order_id = substr($u, strlen('special::order::'));
                $order = App\Order::findOrFail($order_id);
                foreach ($order->topLevelBookings() as $booking) {
                    $map[] = $booking->user_id;
                }
            }
        } else {
            $map[] = $u;
        }
    }

    return array_unique($map);
}

function productByString($string, $products = null)
{
	if (is_null($products)) {
		$products = App\Product::all();
	}

	$target = null;
	$target_combo = null;

	$target = $products->filter(function($p) use ($string) {
		return ($p->name == $string);
	})->first();

	if (is_null($target)) {
		$parts = explode(' - ', $string);
		$parts_count = count($parts);

		for ($i = 0; $i < $parts_count - 1; $i++) {
			$substring = join(' - ', array_slice($parts, 0, $i + 1));

			$target = $products->filter(function($p) use ($substring) {
				return ($p->name == $substring);
			})->first();

			if ($target) {
				break;
			}
		}
	}

	if ($target) {
		if ($target->variants->isEmpty() == false) {
			foreach($target->variant_combos as $combo) {
				if ($combo->printableName() == $string) {
					$target_combo = $combo;
					break;
				}
			}
		}

		return [$target, $target_combo];
	}
	else {
		return null;
	}
}

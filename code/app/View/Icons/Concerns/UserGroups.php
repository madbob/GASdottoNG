<?php

namespace App\View\Icons\Concerns;

use App\Group;

trait UserGroups
{
    private static $selectiveUserIcons = null;

    protected static function selectiveGroups()
    {
        if (static::$selectiveUserIcons == null) {
            static::$selectiveUserIcons = [];

            $groups = Group::orderBy('name', 'asc')->where('context', 'user')->get();
            if ($groups->isEmpty() == false) {
                static::$selectiveUserIcons['people'] = (object) [
                    'text' => _i('Aggregazione Utente'),
                    'assign' => function ($obj) {
                        $ret = [];

                        if ($obj->circles->isEmpty()) {
                            $ret[] = 'hidden-people-none';
                        }
                        else {
                            foreach ($obj->circles as $c) {
                                $ret[] = 'hidden-people-' . $c->id;
                            }
                        }

                        return $ret;
                    },
                    'options' => function ($objs) use ($groups) {
                        $ret = [];

                        $ret['hidden-people-none'] = _i('Senza Aggregazioni');

                        foreach ($groups as $group) {
                            foreach ($group->circles()->orderBy('name', 'asc')->get() as $circle) {
                                $ret['hidden-people-' . $circle->id] = sprintf('%s - %s', $group->printableName(), $circle->printableName());
                            }
                        }

                        return $ret;
                    },
                ];
            }
        }

        return static::$selectiveUserIcons;
    }
}

<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Circle;

trait InCircles
{
    public function circles(): BelongsToMany
    {
        return $this->belongsToMany(Circle::class)->with('group');
    }

    /*
        Restituisce un array con i Circle assegnati, suddivisi per Group di
        riferimento.
        Se uno specifico Group viene richiesto, torna solo i relativi Circles.
        Se nessun Circle è stato assegnato per quel Group, ritorna comunque una
        struttura dati valida
    */
    public function circlesByGroup($group = null)
    {
        $ret = [];

        if (is_null($group)) {
            $iterate = $this->circles;
        }
        else {
            $iterate = $this->circles->where('group_id', $group->id);
        }

        foreach($iterate as $circle) {
            if (isset($ret[$circle->group->id]) == false) {
                $ret[$circle->group->id] = (object) [
                    'group' => $circle->group,
                    'circles' => [],
                ];
            }

            $ret[$circle->group->id]->circles[] = $circle;
        }

        if ($group) {
            if (empty($ret)) {
                $ret = [
                    (object) [
                        'group' => $group,
                        'circles' => [],
                    ]
                ];
            }
            else {
                $ret = $ret[$group->id];
            }
        }

        return $ret;
    }

    public abstract function eligibleGroups();
}

<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Circle;
use App\Group;

trait InCircles
{
    public function circles(): BelongsToMany
    {
        return $this->belongsToMany(Circle::class)->with('group');
    }

    /**
     * Restituisce un array con i Circle assegnati, suddivisi per Group di
     * riferimento.
     * Se uno specifico Group viene richiesto, torna solo i relativi Circles.
     * Se nessun Circle è stato assegnato per quel Group, ritorna comunque una
     * struttura dati valida
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

        foreach ($iterate as $circle) {
            if (isset($ret[$circle->group->id]) === false) {
                $ret[$circle->group->id] = (object) [
                    'group' => $circle->group,
                    'circles' => [],
                ];
            }

            $ret[$circle->group->id]->circles[] = $circle;
        }

        if ($group) {
            if (empty($ret)) {
                $ret = (object) [
                    'group' => $group,
                    'circles' => [],
                ];
            }
            else {
                $ret = $ret[$group->id];
            }
        }

        return $ret;
    }

    /**
     * Trasforma una request nella relativa associazione/disassociazione in
     * aggregazioni e gruppi. In $request ci si aspetta sia l'elenco delle
     * aggregazioni da considerare, ed il relativo elenco di gruppi di
     * abilitare.
     * Da ricordare che quando un utente salva il proprio profilo non
     * necessariamente può modificare tutte le sue relative aggregazioni, dunque
     * non basta fare un sync() sulle relazioni: aggregazione per aggregazione
     * occorre vedere cosa attaccare e cosa sganciare, se non ci sono
     * aggregazioni nella $request vuol dire che non poteva essere modificato
     * nulla e dunque nulla c'è da cambiare
     */
    public function readCircles($request)
    {
        $groups = $request['groups'] ?? [];
        $groups = Group::whereIn('id', $groups)->get();

        foreach ($groups as $group) {
            $key = sprintf('circles__%s__%s', sanitizeId($this->id), sanitizeId($group->id));
            $circles = $request[$key] ?? [];
            $existing = [];

            foreach($this->circles as $attached) {
                if ($attached->group_id == $group->id) {
                    if (in_array($attached->id, $circles)) {
                        $existing[] = $attached->id;
                    }
                    else {
                        $this->circles()->detach($attached->id);
                    }
                }
            }

            $missing = array_diff($circles, $existing);
            foreach($missing as $miss) {
                $this->circles()->attach($miss);
            }
        }
    }

    abstract public function eligibleGroups();
}

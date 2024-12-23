<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\ModifierType;
use App\Modifier;

trait ModifiableTrait
{
    public function modifiers(): MorphMany
    {
        return $this->morphMany('App\Modifier', 'target');
    }

    public function attachEmptyModifier($modtype)
    {
        if ($this->modifiers()->where('modifier_type_id', $modtype->id)->count() == 0) {
            $mod = new Modifier();
            $mod->modifier_type_id = $modtype->id;
            $mod->target_type = get_class($this);
            $mod->target_id = (string) $this->id;
            $mod->applies_type = 'none';
            $mod->definition = '[]';
            $mod->save();

            return $mod;
        }
    }

    private function duplicateModifiers($inherit)
    {
        $ret = $inherit->applicableModificationTypes();

        foreach ($ret as $modtype) {
            if ($this->modifiers()->where('modifier_type_id', $modtype->id)->count() == 0) {
                $replica = $inherit->modifiers()->where('modifier_type_id', $modtype->id)->first()->replicate();
                $replica->target_id = $this->id;
                $replica->target_type = get_class($this);
                $replica->save();
            }
        }

        return $ret;
    }

    public function applicableModificationTypes()
    {
        $inherit = $this->inheritModificationTypes();

        if (! is_null($inherit)) {
            $ret = $this->duplicateModifiers($inherit);
        }
        else {
            $ret = [];
            $same = $this->sameModificationTypes();

            if (! is_null($same)) {
                $modifiers = $same->applicableModificationTypes();
            }
            else {
                $current_class = get_class($this);
                $modifiers = ModifierType::orderBy('name', 'asc')->get()->filter(function ($modtype, $key) use ($current_class) {
                    return in_array($current_class, accessAttr($modtype, 'classes'));
                });
            }

            foreach ($modifiers as $modtype) {
                $ret[] = $modtype;
                $this->attachEmptyModifier($modtype);
            }
        }

        return $ret;
    }

    /*
        Questa va all'occorrenza sovrascritta se si vogliono usare gli stessi
        modificatori (con gli stessi valori) di un altro oggetto
    */
    public function inheritModificationTypes()
    {
        return null;
    }

    /*
        Questa va all'occorrenza sovrascritta se si vogliono usare gli stessi
        tipi di modificatore di un altro oggetto (ma con valori di default
        vuoti)
    */
    public function sameModificationTypes()
    {
        return null;
    }
}

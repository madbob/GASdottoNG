<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

use App\Circle;

trait InCircles
{
    public function circles(): BelongsToMany
    {
        return $this->belongsToMany(Circle::class);
    }

    public function assignCircles(array $request): void
    {
        $assigned = [];

        $eligible_groups = $this->eligibleGroups();
        foreach($eligible_groups as $group) {
            $those = $request['group_' . $group->id] ?? [];
            $assigned = array_merge($assigned, $those);
        }

        $this->circles()->sync($assigned);
    }

    public abstract function eligibleGroups();
}

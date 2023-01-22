<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait FriendTrait
{
    public function friends(): HasMany
    {
        return $this->hasMany('App\User', 'parent_id');
    }

    public function friends_with_trashed(): HasMany
    {
        return $this->hasMany('App\User', 'parent_id')->withTrashed();
    }

    public function isFriend()
    {
        return $this->parent_id != null;
    }
}

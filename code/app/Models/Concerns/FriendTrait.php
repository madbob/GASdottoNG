<?php

namespace App\Models\Concerns;

trait FriendTrait
{
    public function friends()
    {
        return $this->hasMany('App\User', 'parent_id');
    }

    public function friends_with_trashed()
    {
        return $this->hasMany('App\User', 'parent_id')->withTrashed();
    }

    public function isFriend()
    {
        return $this->parent_id != null;
    }
}

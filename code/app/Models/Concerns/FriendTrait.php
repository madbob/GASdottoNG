<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\User;

trait FriendTrait
{
    public function friends(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function friends_with_trashed(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id')->withTrashed();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function scopeTopLevel($query)
    {
        return $query->where('parent_id', null);
    }

    public function isFriend(): boolean
    {
        return $this->parent_id != null;
    }
}

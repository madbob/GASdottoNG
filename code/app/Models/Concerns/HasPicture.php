<?php

namespace App\Models\Concerns;

trait HasPicture
{
    abstract public function getPictureUrlAttribute();

    public function formatAvatar(): string
    {
        return sprintf('<img class="avatar shadow" src="%s" loading="lazy">', $this->picture_url);
    }
}

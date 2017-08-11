<?php

namespace App\Listeners;

use App\Events\SluggableCreating;

class SlugModel
{
    public function __construct()
    {
        //
    }

    public function handle(SluggableCreating $event)
    {
        if (empty($event->sluggable->id)) {
            $event->sluggable->id = $event->sluggable->getSlugID();
        }
    }
}

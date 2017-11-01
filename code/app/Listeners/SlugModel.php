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
            $id = $template = $event->sluggable->getSlugID();
            $class = get_class($event->sluggable);
            $index = 1;

            do {
                $test = $class::where('id', $id)->withTrashed()->first();
                if ($test == null)
                    break;

                $id = $template . '-' . $index++;
            } while(true);

            $event->sluggable->id = $id;
        }
    }
}

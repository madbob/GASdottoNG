<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

trait SluggableID
{
    public function getSlugID()
    {
        $append = '';
        $index = 1;
        $classname = get_class($this);

        while(true) {
            $slug = Str::slug($this->name) . $append;

			// @phpstan-ignore-next-line
            if ($classname::withoutGlobalScope('gas')->where('id', $slug)->first() != null) {
                $append = '_' . $index;
                $index++;
            }
            else {
                break;
            }
        }

        return $slug;
    }
}

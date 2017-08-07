<?php

namespace App;

trait SluggableID
{
    public function getSlugID()
    {
        $append = '';
        $index = 1;
        $classname = get_class($this);

        while(true) {
            $slug = str_slug($this->name) . $append;
            if ($classname::where('id', $slug)->first() != null) {
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

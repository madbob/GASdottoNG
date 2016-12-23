<?php

namespace App;

trait SluggableID
{
    public function getSlugID()
    {
        return str_slug($this->name);
    }
}

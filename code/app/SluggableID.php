<?php

namespace app;

trait SluggableID
{
    public function getSlugID()
    {
        return str_slug($this->name);
    }
}

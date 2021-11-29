<?php

namespace App\Parameters\Config;

class Language extends Config
{
    public function identifier()
    {
        return 'language';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return 'it_IT';
    }
}

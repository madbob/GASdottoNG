<?php

namespace App\Parameters\Config;

class EsIntegration extends Config
{
    public function identifier()
    {
        return 'es_integration';
    }

    public function type()
    {
        return 'boolean';
    }

    public function default()
    {
        return 0;
    }
}

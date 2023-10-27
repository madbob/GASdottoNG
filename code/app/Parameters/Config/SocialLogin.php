<?php

namespace App\Parameters\Config;

class SocialLogin extends Config
{
    public function identifier()
    {
        return 'social_login';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'providers' => []
        ];
    }
}

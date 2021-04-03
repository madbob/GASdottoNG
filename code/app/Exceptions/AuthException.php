<?php

namespace App\Exceptions;

class AuthException extends \Exception
{
    public function __construct($status)
    {
        parent::__construct("Not authorized", $status);
    }

    public function status()
    {
        return $this->getCode();
    }

}

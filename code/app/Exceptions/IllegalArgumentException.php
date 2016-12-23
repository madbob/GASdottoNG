<?php

namespace App\Exceptions;

class IllegalArgumentException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }

    public function status()
    {
        return 500;
    }
}

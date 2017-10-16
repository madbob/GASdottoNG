<?php

namespace App\Exceptions;

class IllegalArgumentException extends \Exception
{
    private $argument;

    public function __construct($message, $argument = '')
    {
        parent::__construct($message);
        $this->argument = $argument;
    }

    public function status()
    {
        return 500;
    }

    public function getArgument()
    {
        return $this->argument;
    }
}

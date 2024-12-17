<?php

namespace App\Exceptions;

class MissingFieldException extends \Exception
{
    public function __construct($status)
    {
        parent::__construct(_i('Colonna obbligatoria non specificata'), $status);
    }

    public function status()
    {
        return $this->getCode();
    }
}

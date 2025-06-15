<?php

namespace App\Exceptions;

class MissingFieldException extends \Exception
{
    public function __construct($status)
    {
        parent::__construct(__('export.help.mandatory_column_error'), $status);
    }

    public function status()
    {
        return $this->getCode();
    }
}

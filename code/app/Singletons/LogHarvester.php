<?php

namespace App\Singletons;

/*
    Da usare per interagire coi logs accumulati durante l'esecuzione
*/
class LogHarvester
{
    private $stack = [];

    public function push($message)
    {
        $this->stack[] = $message;
    }

    public function reset()
    {
        $this->stack = [];
    }

    public function last()
    {
        if (empty($this->stack)) {
            return '';
        }
        else {
            $length = count($this->stack);

            return $this->stack[$length - 1];
        }
    }
}

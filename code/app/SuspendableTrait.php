<?php

namespace App;

trait SuspendableTrait
{
    public function setStatus($status, $deleted_at, $suspended_at)
    {
        switch($status) {
            case 'active':
                $this->suspended_at = null;
                $this->deleted_at = null;
                break;
            case 'suspended':
                $this->suspended_at = decodeDate($suspended_at) ?: date('Y-m-d');
                $this->deleted_at = null;
                break;
            case 'deleted':
                $this->suspended_at = null;
                $this->deleted_at = decodeDate($deleted_at) ?: date('Y-m-d');
                break;
        }
    }

    public function plainStatus()
    {
        if (is_null($this->suspended_at) && is_null($this->deleted_at)) {
            return 'active';
        }
        else if (is_null($this->suspended_at) == false) {
            return 'suspended';
        }
        else if (is_null($this->deleted_at) == false) {
            return 'deleted';
        }
    }

    public function printableStatus()
    {
        switch($this->plainStatus()) {
            case 'active':
                return _i('Attivo');
            case 'suspended':
                return _i('Sospeso');
            case 'deleted':
                return _i('Cessato');
        }
    }
}

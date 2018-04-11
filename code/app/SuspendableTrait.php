<?php

namespace App;

trait SuspendableTrait {
    public function setStatus($status, $deleted_at)
    {
        switch($status) {
            case 'active':
                $this->suspended = false;
                $this->deleted_at = null;
                break;
            case 'suspended':
                $this->suspended = true;
                $this->deleted_at = date('Y-m-d');
                break;
            case 'deleted':
                $this->suspended = false;
                $this->deleted_at = !empty($deleted_at) ? decodeDate($deleted_at) : date('Y-m-d');
                break;
        }
    }
}

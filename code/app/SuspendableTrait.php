<?php

namespace App;

trait SuspendableTrait {
    public function setStatus($status, $deleted_at, $suspended_at)
    {
        switch($status) {
            case 'active':
                $this->suspended_at = null;
                $this->deleted_at = null;
                break;
            case 'suspended':
                $this->suspended_at = !empty($suspended_at) ? decodeDate($suspended_at) : date('Y-m-d');
                $this->deleted_at = null;
                break;
            case 'deleted':
                $this->suspended_at = null;
                $this->deleted_at = !empty($deleted_at) ? decodeDate($deleted_at) : date('Y-m-d');
                break;
        }
    }
}

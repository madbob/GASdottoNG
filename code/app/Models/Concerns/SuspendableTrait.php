<?php

namespace App\Models\Concerns;

trait SuspendableTrait
{
    public function setStatus($status, $deleted_at, $suspended_at)
    {
        switch ($status) {
            case 'active':
                $this->suspended_at = null;
                $this->deleted_at = null;
                break;
            case 'removed':
                $this->suspended_at = decodeDate($suspended_at) ?: date('Y-m-d');
                $this->deleted_at = decodeDate($deleted_at) ?: date('Y-m-d');
                break;
            case 'suspended':
                $this->suspended_at = decodeDate($suspended_at) ?: date('Y-m-d');
                $this->deleted_at = null;
                break;
            case 'deleted':
                $this->suspended_at = null;
                $this->deleted_at = decodeDate($deleted_at) ?: date('Y-m-d');
                break;
            default:
                throw new \InvalidArgumentException("Stato non valido: " . $status);
        }
    }

    public function plainStatus()
    {
        $ret = '';

        if (is_null($this->suspended_at) && is_null($this->deleted_at)) {
            $ret = 'active';
        }
        elseif ($this->suspended_at != null && $this->deleted_at != null) {
            $ret = 'removed';
        }
        elseif ($this->suspended_at != null) {
            $ret = 'suspended';
        }
        elseif ($this->deleted_at != null) {
            $ret = 'deleted';
        }

        return $ret;
    }

    public function printableStatus()
    {
        return __('texts.user.statuses.' . $this->plainStatus());
    }

    public function scopeFullEnabled($query)
    {
        $query->whereNull('deleted_at')->whereNull('suspended_at');
    }
}

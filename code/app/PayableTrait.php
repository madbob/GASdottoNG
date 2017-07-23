<?php

namespace App;

trait PayableTrait
{
    public function movements()
    {
        return $this->morphMany('App\Movement', 'target');
    }

    public function deleteMovements()
    {
        $this->movements()->delete();
    }

    public function queryMovements($query = null, $type = 'all')
    {
        $id = $this->id;
        $class = get_class($this);

        if ($query == null)
            $query = Movement::orderBy('created_at', 'desc');

        switch($type) {
            case 'all':
                $query->where(function($query) use ($id, $class) {
                    $query->where(function($query) use ($id, $class) {
                        $query->where('sender_type', $class)->where('sender_id', $id);
                    })->orWhere(function($query) use ($id, $class) {
                        $query->where('target_type', $class)->where('target_id', $id);
                    });
                });
                break;

            case 'sender':
                $query->where(function($query) use ($id, $class) {
                    $query->where('sender_type', $class)->where('sender_id', $id);
                });
                break;

            case 'target':
                $query->where(function($query) use ($id, $class) {
                    $query->where('target_type', $class)->where('target_id', $id);
                });
                break;
        }

        return $query;
    }
}

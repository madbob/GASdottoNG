<?php

namespace app;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;

class Contact extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    public function target()
    {
        return $this->morphsTo();
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $target->id, $this->name);
    }
}

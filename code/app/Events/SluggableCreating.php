<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

use App\SluggableID;

class SluggableCreating
{
    use SerializesModels;

    public $sluggable;

    public function __construct(Model $sluggable)
    {
        $this->sluggable = $sluggable;
    }
}

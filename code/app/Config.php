<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Config extends Model
{
    use HierarcableTrait, Cachable;
}

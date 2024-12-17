<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\HierarcableTrait;

class Config extends Model
{
    use Cachable, HierarcableTrait;
}

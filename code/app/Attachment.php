<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Attachment extends Model
{
        use GASModel;

        public function attached()
        {
                return $this->morphTo('target');
        }

        public function getPathAttribute()
        {
                return sprintf('%s/%s', $this->attached->filesPath(), $this->filename);
        }
}

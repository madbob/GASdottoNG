<?php

namespace app;

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

    public function getDownloadUrlAttribute()
    {
        if (!empty($this->url)) {
            return $this->url;
        } else {
            return url('attachments/download/'.$this->id);
        }
    }
}

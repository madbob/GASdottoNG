<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

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

    public function isImage()
    {
        $file = $this->path;

        if (file_exists($file)) {
            $mime = mime_content_type($file);
            if (strncmp($mime, 'image/', 6) == 0)
                return true;
        }

        return false;
    }

    public function getSize()
    {
        if ($this->isImage()) {
            $file = $this->path;
            if (file_exists($file)) {
                $size = getimagesize($file);
                if ($size != null)
                    return array_slice($size, 0, 2);
            }
        }
        else {
            Log::error('Richiesta dimensione per allegato non immagine');
        }

        return [0, 0];
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

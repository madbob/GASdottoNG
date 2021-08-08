<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Log;
use Auth;

class Attachment extends Model
{
    use GASModel, Cachable;

    public function attached()
    {
        if ($this->target_type && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->target_type))) {
            // @phpstan-ignore-next-line
            return $this->morphTo('target')->withoutGlobalScopes()->withTrashed();
        }
        else {
            // @phpstan-ignore-next-line
            return $this->morphTo('target')->withoutGlobalScopes();
        }
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'attachments_access');
    }

    public function hasAccess($user = null)
    {
        if ($this->users->isEmpty()) {
            return true;
        }

        if (is_null($user)) {
            $user = Auth::user();
        }

        return ($this->users()->where('users.id', $user->id)->count() != 0);
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

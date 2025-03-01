<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
    @property-read Gas|Invoice|Notification|Order|Supplier|null $attached
 */
class Attachment extends Model
{
    use Cachable, GASModel;

    public function attached(): MorphTo
    {
        if ($this->target_type && hasTrait($this->target_type, SoftDeletes::class)) {
            return $this->morphTo('target')->withoutGlobalScopes()->withTrashed();
        }
        else {
            return $this->morphTo('target')->withoutGlobalScopes();
        }
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\User', 'attachments_access');
    }

    public function getTypeAttribute()
    {
        if (empty($this->url) === false) {
            return 'url';
        }
        else {
            return 'file';
        }
    }

    public function hasAccess($user = null)
    {
        if ($this->users->isEmpty()) {
            return true;
        }

        if (is_null($user)) {
            $user = Auth::user();
        }

        return $this->users()->where('users.id', $user->id)->count() != 0;
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
            if ($mime && strncmp($mime, 'image/', 6) == 0) {
                return true;
            }
        }

        return false;
    }

    public function getSize()
    {
        $ret = [0, 0];

        try {
            if ($this->isImage()) {
                $file = $this->path;
                $size = getimagesize($file);
                if ($size) {
                    return array_slice($size, 0, 2);
                }
            }

            Log::error('Richiesta dimensione per allegato non immagine');
        }
        catch (\Exception $e) {
            Log::error('Impossibile recuperare dimensione allegato ' . $this->id);
        }

        return $ret;
    }

    public function getDownloadUrlAttribute()
    {
        if (empty($this->url) === false) {
            return $this->url;
        }
        else {
            return url('attachments/download/'.$this->id);
        }
    }
}

<?php

/*
    Trait usato da tutti i modelli cui è possibile allegare files, che sono a
    loro volta astratti dal model Attachment (che ha una relazione polimorfica
    nei confronti dell'elemento di riferimento)
*/

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use App\Attachment;

trait AttachableTrait
{
    public function attachments()
    {
        $relation = $this->morphMany('App\Attachment', 'target')->orderBy('name', 'asc');
        $attachments = $relation->get();

        if ($attachments->isEmpty()) {
            $extra = $this->defaultAttachments();
            foreach ($extra as $e) {
                $e->target_id = $this->id;
                $e->target_type = get_class($this);
                $e->save();
                $relation->save($e);
            }
        }

        if ($this->attachmentPermissionGranted() == false) {
            $user = Auth::user();
            if ($user) {
                $relation->where(function($query) use ($user) {
                    $query->whereDoesntHave('users')->orWhereHas('users', function($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                });
            }
        }

        return $relation;
    }

    private function retrieveAttachment($id)
    {
        if (is_null($id)) {
            $attachment = new Attachment();
            $attachment->target_type = get_class($this);
            $attachment->target_id = (string) $this->id;
        }
        else {
            $attachment = Attachment::findOrFail($id);
            @unlink($attachment->getPathAttribute());
        }

        return $attachment;
    }

    public function attachByRequest($request, $id = null)
    {
        if (is_array($request)) {
            $file = $request['file'] ?? null;
            $name = '';
            $users = $request['users'] ?? [];
            $to_delete = $request['delete_attachment'] ?? [];
        }
        else {
            $file = $request->file('file');
            $name = $request->input('name', '');
            $users = $request->input('users', []);
            $to_delete = $request->input('to_delete', []);
        }

        foreach ($this->attachments()->whereIn('id', $to_delete)->get() as $att) {
            $att->delete();
        }

        if (is_null($file) || $file->isValid() == false) {
            return false;
        }

        $filepath = $this->filesPath();
        if (is_null($filepath)) {
            return false;
        }

        $filename = $file->getClientOriginalName();
        if ($name == '') {
            $name = $filename;
        }

        $file->move($filepath, $filename);

        $attachment = $this->retrieveAttachment($id);
        $attachment->name = $name;
        $attachment->filename = $filename;
        $attachment->save();

        $users = unrollSpecialSelectors($users);
        $attachment->users()->sync($users);

        return $attachment;
    }

    /*
        Questa funzione può essere sovrascritta dalla classe che usa
        questo trait per esplicitare i permessi utente necessari per
        allegare un file ad un oggetto della classe stessa
    */
    protected function requiredAttachmentPermission()
    {
        return null;
    }

    /*
        Questa funzione viene chiamata quando l'oggetto non ha nessun
        allegato, la classe di riferimento può sovrascriverla per
        popolare degli allegati di default
    */
    protected function defaultAttachments()
    {
        return [];
    }

    public function filesPath($create = true)
    {
        $prefix = Str::slug(get_class($this));
        $path = gas_storage_path($prefix . '-' . $this->id);
        if (file_exists($path) == false && $create) {
            if (@mkdir($path, 0750, true) == false) {
                return null;
            }
        }

        return $path;
    }

    public function attachmentPermissionGranted()
    {
        $required = $this->requiredAttachmentPermission();
        if ($required != null) {
            $user = Auth::user();
            if ($user) {
                return $user->can($required, $this);
            }
        }
        else {
            return true;
        }
    }
}

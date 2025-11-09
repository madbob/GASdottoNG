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
    private function fillDefaultAttachments($relation)
    {
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

        return $relation;
    }

    public function rawAttachments()
    {
        return $this->morphMany(Attachment::class, 'target')->orderBy('name', 'asc');
    }

    public function attachments()
    {
        $relation = $this->rawAttachments();
        $relation = $this->fillDefaultAttachments($relation);

        if ($this->attachmentPermissionGranted() === false) {
            $user = Auth::user();
            if ($user) {
                $relation->where(function ($query) use ($user) {
                    $query->whereDoesntHave('users')->orWhereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                });
            }
        }

        return $relation;
    }

    private function retrieveAttachment($id, $new_filename)
    {
        if (is_null($id)) {
            $attachment = new Attachment();
            $attachment->target_type = get_class($this);
            $attachment->target_id = (string) $this->id;
        }
        else {
            $attachment = Attachment::findOrFail($id);

            /*
                Nota bene: questa funzione viene chiamata dopo che un nuovo
                file è stato caricato in sostituzione di uno precedente.
                Dunque, se il vecchio filename corrisponde al nuovo, rischio di
                eliminare anche la nuova versione.
                Questa funzione va comunque chiamata dopo aver salvato con
                successo il nuovo file, altrimenti potrei arrivare qui,
                eliminare la copia vecchia, e se la procedura fallisce mi
                troverei senza il file vecchio né quello nuovo
            */
            if ($new_filename != $attachment->filename) {
                @unlink($attachment->getPathAttribute());
            }
        }

        return $attachment;
    }

    private function pathFor($name)
    {
        $filepath = $this->filesPath();
        if (is_null($filepath)) {
            return false;
        }

        return sprintf('%s/%s', $filepath, $name);
    }

    private function storeFile(&$name, $file)
    {
        $filepath = $this->filesPath();
        if (is_null($filepath)) {
            return false;
        }

        $filename = $file->getClientOriginalName();
        if ($name == '') {
            $name = $filename;
        }

        $file->move($filepath, $filename);

        return $filename;
    }

    private function checkDeletes($request)
    {
        $to_delete = $request['delete_attachment'] ?? [];
        $deletables = $this->attachments()->whereIn('id', $to_delete)->get();

        foreach ($deletables as $att) {
            $att->delete();
        }
    }

    private function checkAssignments($request, $attachment)
    {
        $users = $request['users'] ?? [];
        $users = unrollSpecialSelectors($users);
        $attachment->users()->sync($users);

        return $attachment;
    }

    public function attachByRequest($request, $id = null)
    {
        $file = $request['file'] ?? null;
        $url = $request['url'] ?? null;
        $name = $request['name'] ?? '';

        $this->checkDeletes($request);

        if ($file != null && $file->isValid()) {
            $filename = $this->storeFile($name, $file);
            if ($filename === false) {
                return false;
            }

            $url = '';
        }
        elseif ($url != null) {
            $filename = '';
        }
        else {
            return false;
        }

        $attachment = $this->retrieveAttachment($id, $filename);
        $attachment->name = $name;
        $attachment->filename = $filename;
        $attachment->url = $url;
        $attachment->save();

        $attachment = $this->checkAssignments($request, $attachment);

        return $attachment;
    }

    public function attachByContents($filename, $contents, $id = null)
    {
        $fullpath = $this->pathFor($filename);
        file_put_contents($fullpath, $contents);

        $attachment = $this->retrieveAttachment($id, $filename);
        $attachment->name = $filename;
        $attachment->filename = $filename;
        $attachment->save();

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
        if (file_exists($path) === false && $create) {
            $test = @mkdir($path, 0750, true);
            if ($test === false) {
                return null;
            }
        }

        return $path;
    }

    public function attachmentPermissionGranted(): bool
    {
        $required = $this->requiredAttachmentPermission();
        if ($required) {
            $user = Auth::user();
            if ($user) {
                return $user->can($required, $this);
            }
        }

        return true;
    }

    public function transferAttachmentsFrom(array $others): void
    {
        foreach($others as $other) {
            foreach($other->rawAttachments as $attach) {
                if ($attach->attached->id != $this->id) {
                    if (filled($attach->filename)) {
                        $old_path = $attach->path;
                        $new_path = $this->pathFor($attach->filename);
                        rename($old_path, $new_path);
                    }

                    $attach->attached()->associate($this);
                    $attach->save();
                }
            }
        }
    }
}

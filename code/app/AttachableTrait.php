<?php

namespace App;

use Auth;

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

        return $relation;
    }

    public function attachByRequest($request, $id = null)
    {
        $file = $request->file('file');

        if (is_null($file) || $file->isValid() == false) {
            return false;
        }

        $filepath = $this->filesPath();
        if (is_null($filepath)) {
            return false;
        }

        $filename = $file->getClientOriginalName();
        $name = $request->input('name', '');
        if ($name == '') {
            $name = $filename;
        }

        if (is_null($id)) {
            $attachment = new Attachment();
            $attachment->target_type = get_class($this);
            $attachment->target_id = $this->id;
        }
        else {
            $attachment = Attachment::findOrFail($id);
            @unlink($attachment->getPathAttribute());
        }

        $file->move($filepath, $filename);

        $attachment->name = $name;
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

    public function filesPath()
    {
        $path = gas_storage_path($this->name);
        if (file_exists($path) == false) {
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
            return $user->can($required, $this);
        }
        else {
            return true;
        }
    }
}

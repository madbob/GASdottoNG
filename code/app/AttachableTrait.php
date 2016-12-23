<?php

namespace app;

trait AttachableTrait
{
    public function attachments()
    {
        $relation = $this->morphMany('App\Attachment', 'target');
        $attachments = $relation->get();

        if ($attachments->isEmpty()) {
            $extra = $this->defaultAttachments();
            foreach ($extra as $e) {
                $e->save();
                $relation->save($e);
            }
        }

        return $relation;
    }

    public function attachByRequest($request)
    {
        $file = $request->file('file');

        if ($file == null || $file->isValid() == false) {
            return false;
        }

        $filepath = $this->filesPath();
        if ($filepath == null) {
            return false;
        }

        $filename = $file->getClientOriginalName();
        $file->move($filepath, $filename);

        $name = $request->input('filename', '');
        if ($name == '') {
            $name = $filename;
        }

        $attachment = new Attachment();
        $attachment->name = $name;
        $attachment->filename = $filename;
        $attachment->save();

        $this->attachments()->save($attachment);

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
        $path = sprintf('%s/%s', storage_path(), $this->name);
        if (file_exists($path) == false) {
            if (mkdir($path) == false) {
                return null;
            }
        }

        return $path;
    }

    public function attachmentPermissionGranted()
    {
        if (array_search('App\AllowableTrait', class_uses($this)) !== false) {
            $permission = $this->requiredAttachmentPermission();
            if ($permission == null) {
                return true;
            }

            return $this->userCan($permission);
        } else {
            return true;
        }
    }
}

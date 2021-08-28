<?php

/*
    Questo trait va usato per le notifiche cui sono passati files temporanei da
    allegare alle email, che vanno eliminati una volta conclusa l'operazione
    (che è asincrona).
    Una volta inviata la notifica, il listener AfterNotification provvede a
    recuperare i files e rimuoverli.
*/

namespace App\Notifications;

trait TemporaryFiles
{
    private $files = [];

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }
}

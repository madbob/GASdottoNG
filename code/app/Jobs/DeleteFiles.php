<?php

namespace App\Jobs;

class DeleteFiles extends Job
{
    public $files;

    public function __construct($files)
    {
        parent::__construct();
        $this->files = $files;
    }

    protected function realHandle()
    {
        foreach($this->files as $file) {
            @unlink($file);
        }
    }
}

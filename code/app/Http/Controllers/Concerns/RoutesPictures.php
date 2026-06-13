<?php

namespace App\Http\Controllers\Concerns;

trait RoutesPictures
{
    public function picture($id)
    {
        return $this->easyExecute(function () use ($id) {
            return $this->getBackedService()->picture($id);
        });
    }
}

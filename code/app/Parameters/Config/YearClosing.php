<?php

namespace App\Parameters\Config;

class YearClosing extends Config
{
    public function identifier()
    {
        return 'year_closing';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return date('Y') . '-09-01';
    }

    public function handleSave($gas, $request)
    {
        $gas->setConfig('year_closing', decodeDateMonth($request->input('year_closing')));
    }
}

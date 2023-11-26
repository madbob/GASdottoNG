<?php

namespace App\Parameters\Config;

class Rid extends Config
{
    public function identifier()
    {
        return 'rid';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'iban' => '',
            'id' => '',
            'org' => ''
        ];
    }

    public function handleSave($gas, $request)
    {
        if ($request->has('enable_rid')) {
            $rid_info = (object) [
                'iban' => $request->input('rid->iban'),
                'id' => $request->input('rid->id'),
                'org' => $request->input('rid->org'),
            ];
        }
        else {
            $rid_info = (object) [
                'iban' => '',
                'id' => '',
                'org' => '',
            ];
        }

        $gas->setConfig('rid', $rid_info);
    }
}

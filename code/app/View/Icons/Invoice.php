<?php

namespace App\View\Icons;

class Invoice extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'clock' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'pending';
                },
                'text' => _i('In Attesa'),
            ],
            'pin-angle' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'to_verify';
                },
                'text' => _i('Da Verificare'),
            ],
            'search' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'verified';
                },
                'text' => _i('Verificata'),
            ],
            'check' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'payed';
                },
                'text' => _i('Pagata'),
            ],
        ];

        /*
            Poiché fatture in ingresso (Invoice) e in uscita (Receipt) sono
            visualizzate nello stesso elenco, se queste ultime sono attive
            abilito delle icone distintive per permettere di riconoscerle
            al volo
        */
        if ($user->gas->hasFeature('extra_invoicing')) {
            $ret['arrow-left'] = (object) [
                'test' => function ($obj) {
                    return true;
                },
                'text' => _i('In Entrata'),
            ];
        }

        return $ret;
    }
}

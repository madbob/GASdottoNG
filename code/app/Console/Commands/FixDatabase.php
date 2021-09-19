<?php

/*
    Questo comando viene usato per aggiornare i database delle istanze in
    produzione per eventuali modifiche allo schema.
    Il suo contenuto cambia nel tempo, man mano che avvengono gli aggiornamenti.
*/

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Log;

use App\MovementType;

class FixDatabase extends Command
{
    protected $signature = 'fix:database';
    protected $description = 'Sistema le informazioni sul DB per completare il deploy';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (MovementType::find('booking-payment-adjust') == null) {
            $type = new MovementType();
            $type->id = 'booking-payment-adjust';
            $type->name = 'Aggiustamento pagamento prenotazione da parte di un socio';
            $type->sender_type = 'App\User';
            $type->target_type = 'App\Booking';
            $type->allow_negative = true;
            $type->fixed_value = null;
            $type->visibility = false;
            $type->system = true;
            $type->function = json_encode(
                [
                    (object) [
                        'method' => 'credit',
                        'sender' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'decrement',
                                    'field' => 'bank'
                                ],
                            ]
                        ],
                        'target' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'increment',
                                    'field' => 'bank'
                                ],
                            ]
                        ],
                        'master' => (object) [
                            'operations' => [
                                (object) [
                                    'operation' => 'increment',
                                    'field' => 'suppliers'
                                ],
                            ]
                        ],
                    ],
                ]
            );
            $type->save();
        }
    }
}

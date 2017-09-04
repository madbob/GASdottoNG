<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\MovementType;

class MovementTypesSeeder extends Seeder
{
    public function run()
    {
        $type = new MovementType();
        $type->id = 'user-credit';
        $type->name = 'Deposito di credito da parte di un socio';
        $type->sender_type = null;
        $type->target_type = 'App\User';
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => []
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
                                'field' => 'cash'
                            ],
                        ]
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => []
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
                                'field' => 'bank'
                            ],
                        ]
                    ]
                ]
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'user-decredit';
        $type->name = 'Reso credito per un socio';
        $type->sender_type = 'App\User';
        $type->target_type = null;
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                        ]
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                        ]
                    ]
                ]
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'generic-put';
        $type->name = 'Versamento sul conto';
        $type->sender_type = null;
        $type->target_type = 'App\Gas';
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'gas-expense';
        $type->name = 'Acquisto/spesa GAS';
        $type->sender_type = 'App\Gas';
        $type->target_type = null;
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'user-refund';
        $type->name = 'Rimborso spesa socio';
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\User';
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'credit',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
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
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'donation-to-gas';
        $type->name = 'Donazione al GAS';
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => []
                    ],
                    'target' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'increment',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'increment',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
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
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'donation-from-gas';
        $type->name = 'Donazione dal GAS';
        $type->sender_type = 'App\Gas';
        $type->target_type = null;
        $type->allow_negative = false;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'cash',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'cash'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
                (object) [
                    'method' => 'bank',
                    'sender' => (object) [
                        'operations' => [
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'bank'
                            ],
                            (object) [
                                'operation' => 'decrement',
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'target' => (object) [
                        'operations' => []
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ],
            ]
        );
        $type->save();

        $type = new MovementType();
        $type->id = 'supplier-rounding';
        $type->name = 'Arrotondamento/sconto fornitore';
        $type->sender_type = 'App\Supplier';
        $type->target_type = 'App\Gas';
        $type->allow_negative = true;
        $type->fixed_value = null;
        $type->function = json_encode(
            [
                (object) [
                    'method' => 'bank',
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
                                'field' => 'gas'
                            ],
                        ]
                    ],
                    'master' => (object) [
                        'operations' => []
                    ]
                ]
            ]
        );
        $type->save();
    }
}

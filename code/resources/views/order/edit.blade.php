<?php $summary = $order->reduxData() ?>

<form class="form-horizontal main-form order-editor" method="PUT" action="{{ route('orders.update', $order->id) }}">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />

    <div class="row">
        <div class="col-md-6 col-lg-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])

            @include('commons.staticstringfield', [
                'obj' => $order,
                'name' => 'internal_number',
                'label' => _i('Numero'),
                'help_popover' => _i("Numero progressivo automaticamente assegnato ad ogni ordine"),
            ])

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.textarea', [
                    'obj' => $order,
                    'name' => 'comment',
                    'label' => _i('Commento'),
                    'rows' => 2,
                    'help_popover' => _i("Eventuale testo informativo da visualizzare nel titolo dell'ordine. Se più lungo di %d caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.", [App\Order::longCommentLimit()]),
                ])

                @include('commons.datefield', ['obj' => $order, 'name' => 'start', 'label' => _i('Data Apertura'), 'mandatory' => true])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'end',
                    'label' => _i('Data Chiusura'),
                    'mandatory' => true,
                    'extras' => [
                        'data-enforce-after' => '.date[name=start]'
                    ],
                    'help_popover' => _i("Data di chiusura dell'ordine. Al termine del giorno qui indicato, l'ordine sarà automaticamente impostato nello stato \"Prenotazioni Chiuse\""),
                ])

                @include('commons.datefield', [
                    'obj' => $order,
                    'name' => 'shipping',
                    'label' => _i('Data Consegna'),
                    'extras' => [
                        'data-enforce-after' => '.date[name=end]'
                    ]
                ])

                @if($currentgas->hasFeature('shipping_places') && $order->aggregate->orders()->count() == 1)
                    <!--
                        Se l'ordine è aggregato ad altri, i luoghi di consegna
                        si editano una volta per tutti direttamente nel pannello
                        dell'aggregato
                    -->
                    @include('commons.selectobjfield', [
                        'obj' => $order,
                        'name' => 'deliveries',
                        'label' => _i('Luoghi di Consegna'),
                        'mandatory' => false,
                        'objects' => $currentgas->deliveries,
                        'multiple_select' => true,
                        'extra_selection' => ['' => _i('Non limitare luogo di consegna')],
                        'help_popover' => _i("Selezionando uno o più luoghi di consegna, l'ordine sarà visibile solo agli utenti che hanno attivato quei luoghi. Se nessun luogo viene selezionato, l'ordine sarà visibile a tutti. Tenere premuto Ctrl per selezionare più voci."),
                    ])
                @endif

                @if($currentgas->booking_contacts == 'manual')
                    <?php

                    $contactable_users = new Illuminate\Support\Collection();

                    foreach(App\Role::rolesByClass('App\Supplier') as $role) {
                        $contactable_users = $contactable_users->merge($role->usersByTarget($order->supplier));
                    }

                    $contactable_users = $contactable_users->sortBy('surname')->unique();

                    ?>

                    @include('commons.selectobjfield', [
                        'obj' => $order,
                        'name' => 'users',
                        'label' => _i('Contatti'),
                        'mandatory' => false,
                        'objects' => $contactable_users,
                        'multiple_select' => true,
                        'help_text' => _i("I contatti degli utenti selezionati saranno mostrati nel pannello delle prenotazioni. Tenere premuto Ctrl per selezionare più utenti")
                    ])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    <?php

                    if ($order->aggregate->gas()->count() > 1) {
                        $keep_open_packages_values = [
                            'no' => (object) [
                                'name' => _i('No, ignora la dimensione delle confezioni'),
                                'checked' => ($order->keep_open_packages == 'no'),
                            ],
                            'each' => (object) [
                                'name' => _i('Si, e ogni GAS gestisce le sue confezioni'),
                                'checked' => ($order->keep_open_packages == 'each'),
                            ],
                            'all' => (object) [
                                'name' => _i('Si, e contempla le quantità prenotate da parte di tutti i GAS'),
                                'checked' => ($order->keep_open_packages == 'all'),
                            ],
                        ];
                    }
                    else {
                        $keep_open_packages_values = [
                            'no' => (object) [
                                'name' => _i('No, ignora la dimensione delle confezioni'),
                                'checked' => ($order->keep_open_packages == 'no'),
                            ],
                            'each' => (object) [
                                'name' => _i('Si, permetti eventuali altre prenotazioni'),
                                'checked' => ($order->keep_open_packages == 'each'),
                            ],
                        ];
                    }

                    ?>

                    @include('commons.radios', [
                        'name' => 'keep_open_packages',
                        'label' => _i('Forza completamento confezioni'),
                        'help_popover' => _i("Se questa opzione viene abilitata, alla chiusura dell'ordine sarà verificato se ci sono prodotti la cui quantità complessivamente ordinata non è multipla della dimensione della relativa confezione. Se si, l'ordine resterà aperto e sarà possibile per gli utenti prenotare solo quegli specifici prodotti finché non si raggiunge la quantità desiderata"),
                        'values' => $keep_open_packages_values,
                    ])
                @endif
            @else
                @if(!empty($order->comment))
                    @include('commons.staticstringfield', ['obj' => $order, 'name' => 'comment', 'label' => _i('Commento')])
                @endif

                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'start', 'label' => _i('Data Apertura')])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'end', 'label' => _i('Data Chiusura')])
                @include('commons.staticdatefield', ['obj' => $order, 'name' => 'shipping', 'label' => _i('Data Consegna')])

                @if($order->deliveries()->count() != 0 && $order->aggregate->orders()->count() == 1)
                    @include('commons.staticobjectslistfield', ['obj' => $order, 'name' => 'deliveries', 'label' => _i('Luoghi di Consegna')])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    @include('commons.staticboolfield', ['obj' => $order, 'name' => 'keep_open_packages', 'label' => _i('Forza completamento confezioni')])
                @endif
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-md-6 col-lg-4">
            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                @include('commons.modifications', ['obj' => $order])
            @else
                @include('commons.staticmodifications', ['obj' => $order])
            @endif

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('supplier.movements', $order->supplier))
                @include('commons.movementfield', [
                    'obj' => $order->payment,
                    'name' => 'payment_id',
                    'label' => _i('Pagamento'),
                    'default' => \App\Movement::generate('order-payment', $currentgas, $order, $summary->price_delivered ?? 0),
                    'to_modal' => [
                        'amount_editable' => true
                    ],
                    'help_popover' => _i("Da qui è possibile immettere il movimento contabile di pagamento dell'ordine nei confronti del fornitore, che andrà ad alterare il relativo saldo"),
                ])
            @else
                @include('commons.staticmovementfield', [
                    'obj' => $order->payment,
                    'label' => 'Pagamento'
                ])
            @endif
        </div>
        <div class="col-md-6 col-lg-4">
            @include('order.files', ['order' => $order])
        </div>
    </div>

    <hr>

    @include('order.summary', ['order' => $order, 'summary' => $summary])
    @include('order.annotations', ['order' => $order])

    @include('commons.formbuttons', [
        'no_delete' => $order->isActive() == false,
        'left_buttons' => [
            (object) [
                'label' => _i('Esporta'),
                'url' => $order->exportableURL(),
                'class' => ''
            ]
        ]
    ])
</form>

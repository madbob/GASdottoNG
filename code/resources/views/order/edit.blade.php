<?php

$summary = $master_summary->orders[$order->id];

$custom_buttons = [
    [
        'label' => _i('Esporta'),
        'classes' => ['float-start', 'link-button', 'me-2'],
        'attributes' => ['data-link' => $order->exportableURL()]
    ]
];

if ($order->bookings()->count() > 0) {
    $nodelete = true;

    $custom_buttons[] = [
        'color' => 'danger',
        'classes' => ['async-modal'],
        'label' => _i('Elimina'),
        'attributes' => [
            'data-modal-url' => route('orders.nodestroy', $order->id),
        ]
    ];
}
else {
    $nodelete = false;
}

?>

<x-larastrap::mform :obj="$order" classes="order-editor" method="PUT" :action="route('orders.update', $order->id)" :nodelete="$nodelete" :other_buttons="$custom_buttons">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />
    <input type="hidden" name="post-saved-function" value="afterAggregateChange" class="skip-on-submit">

    <div class="row">
        <div class="col-12 col-lg-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'label' => _i('Fornitore')])
            <x-larastrap::text name="internal_number" :label="_i('Numero')" readonly disabled :pophelp="_i('Numero progressivo automaticamente assegnato ad ogni ordine')" />

            <?php

            $keep_open_packages_values = [
                'no' => _i('No, ignora la dimensione delle confezioni'),
                'each' => _i('Si, permetti eventuali altre prenotazioni'),
            ];

            if ($order->aggregate->gas()->count() > 1) {
                $keep_open_packages_values['all'] = _i('Si, e contempla le quantità prenotate da parte di tutti i GAS');
            }

            ?>

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                <x-larastrap::textarea name="comment" :label="_i('Commento')" maxlength="190" rows="2" :pophelp="_i('Eventuale testo informativo da visualizzare nel titolo dell\'ordine. Se più lungo di %d caratteri, il testo viene invece incluso nel pannello delle relative prenotazioni.', [longCommentLimit()])" />
                <x-larastrap::datepicker name="start" :label="_i('Data Apertura')" required />
                <x-larastrap::datepicker name="end" :label="_i('Data Chiusura')" required :attributes="['data-enforce-after' => '.date[name=start]']" :pophelp="_i('Data di chiusura dell\'ordine. Al termine del giorno qui indicato, l\'ordine sarà automaticamente impostato nello stato Prenotazioni Chiuse')" />
                <x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" :attributes="['data-enforce-after' => '.date[name=end]']" />

                @if($currentgas->booking_contacts == 'manual')
                    <?php

                    $contactable_users = new Illuminate\Support\Collection();
                    foreach(rolesByClass('App\Supplier') as $role) {
                        $contactable_users = $contactable_users->merge($role->usersByTarget($order->supplier));
                    }

                    $contactable_users = $contactable_users->sortBy('surname')->unique();

                    ?>

                    <x-larastrap::selectobj name="users" :label="_i('Contatti')" :options="$contactable_users" multiple :pophelp="_i('I contatti degli utenti selezionati saranno mostrati nel pannello delle prenotazioni. Tenere premuto Ctrl per selezionare più utenti')" />
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    <x-larastrap::radios name="keep_open_packages" :label="_i('Forza completamento confezioni')" :options="$keep_open_packages_values" classes="btn-group-vertical" :pophelp="_i('Se questa opzione viene abilitata, alla chiusura dell\'ordine sarà verificato se ci sono prodotti la cui quantità complessivamente ordinata non è multipla della dimensione della relativa confezione. Se si, l\'ordine resterà aperto e sarà possibile per gli utenti prenotare solo quegli specifici prodotti finché non si raggiunge la quantità desiderata')" />
                @endif
            @else
                @if(!empty($order->comment))
                    <x-larastrap::text name="comment" :label="_i('Commento')" readonly disabled />
                @endif

                <x-larastrap::datepicker name="start" :label="_i('Data Apertura')" readonly disabled />
                <x-larastrap::datepicker name="end" :label="_i('Data Chiusura')" readonly disabled />
                <x-larastrap::datepicker name="shipping" :label="_i('Data Consegna')" readonly disabled />

                @if($order->circles()->count() != 0 && $order->aggregate->orders()->count() == 1)
                    @include('order.partials.groups', ['order' => $order, 'readonly' => true])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    <x-larastrap::text :label="_i('Forza completamento confezioni')" :value="$keep_open_packages_values[$order->keep_open_packages]" readonly disabled />
                @endif
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-12 col-lg-4">
            @include('order.partials.groups', ['order' => $order, 'readonly' => false])

            @include('commons.modifications', [
                'obj' => $order,
                'skip_void' => true,
            ])

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
        <div class="col-12 col-lg-4">
            @include('order.files', ['order' => $order])
        </div>
    </div>

    <hr>

    @include('order.summary', ['order' => $order, 'master_summary' => $master_summary])
    @include('order.annotations', ['order' => $order])
</x-larastrap::mform>

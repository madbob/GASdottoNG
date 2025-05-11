<?php

$summary = $master_summary->orders[$order->id];

$custom_buttons = [
    [
        'tlabel' => 'generic.export',
        'classes' => ['float-start', 'link-button', 'me-2'],
        'attributes' => ['data-link' => $order->exportableURL()]
    ]
];

if ($order->bookings()->count() > 0) {
    $nodelete = true;

    $custom_buttons[] = [
        'color' => 'danger',
        'classes' => ['async-modal'],
        'tlabel' => 'generic.remove',
        'attributes' => [
            'data-modal-url' => route('orders.nodestroy', $order->id),
        ]
    ];
}
else {
    $nodelete = false;
}

$shipped_modifiers = $order->applyModifiers($master_summary, 'shipped');

?>

<x-larastrap::mform :obj="$order" classes="order-editor" method="PUT" :action="route('orders.update', $order->id)" :nodelete="$nodelete" :other_buttons="$custom_buttons">
    <input type="hidden" name="order_id" value="{{ $order->id }}" />
    <input type="hidden" name="post-saved-function" value="afterAggregateChange" class="skip-on-submit">

    <div class="row">
        <div class="col-12 col-lg-4">
            @include('commons.staticobjfield', ['obj' => $order, 'name' => 'supplier', 'tlabel' => 'orders.supplier'])
            <x-larastrap::text name="internal_number" tlabel="generic.number" readonly disabled tpophelp="orders.help.number" />

            <?php

            $keep_open_packages_values = [
                'no' => __('orders.packages.ignore'),
                'each' => __('orders.packages.permit'),
            ];

            if ($order->aggregate->gas()->count() > 1) {
                $keep_open_packages_values['all'] = __('orders.packages.permit_all');
            }

            ?>

            @if(in_array($order->status, ['suspended', 'open', 'closed']))
                <x-larastrap::textarea name="comment" tlabel="generic.comment" maxlength="190" rows="2" :pophelp="__('help.comment', ['limit' => longCommentLimit()])" />
                <x-larastrap::datepicker name="start" tlabel="orders.dates.start" required />
                <x-larastrap::datepicker name="end" tlabel="orders.dates.end" required :attributes="['data-enforce-after' => '.date[name=start]']" tpophelp="orders.help.end" />
                <x-larastrap::datepicker name="shipping" tlabel="orders.dates.shipping" :attributes="['data-enforce-after' => '.date[name=end]']" />

                @if($currentgas->booking_contacts == 'manual')
                    <?php

                    $contactable_users = new Illuminate\Support\Collection();
                    foreach(rolesByClass('App\Supplier') as $role) {
                        $contactable_users = $contactable_users->merge($role->usersByTarget($order->supplier));
                    }

                    $contactable_users = $contactable_users->sortBy('surname')->unique();

                    ?>

                    <x-larastrap::selectobj name="users" tlabel="generic.contacts" :options="$contactable_users" multiple tpophelp="orders.help.contacts" />
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    <x-larastrap::radios name="keep_open_packages" tlabel="orders.handle_packages" :options="$keep_open_packages_values" classes="btn-group-vertical" tpophelp="orders.help.handle_packages" />
                @endif
            @else
                @if(!empty($order->comment))
                    <x-larastrap::text name="comment" tlabel="generic.comment" readonly disabled />
                @endif

                <x-larastrap::datepicker name="start" tlabel="orders.dates.start" readonly disabled />
                <x-larastrap::datepicker name="end" tlabel="orders.dates.end" readonly disabled />
                <x-larastrap::datepicker name="shipping" tlabel="orders.dates.shipping" readonly disabled />

                @if($order->circles()->count() != 0 && $order->aggregate->orders()->count() == 1)
                    @include('order.partials.groups', ['order' => $order, 'readonly' => true])
                @endif

                @if($order->products()->where('package_size', '!=', 0)->count() != 0)
                    <x-larastrap::text tlabel="orders.handle_packages" :value="$keep_open_packages_values[$order->keep_open_packages]" readonly disabled />
                @endif
            @endif

            @include('commons.orderstatus', ['order' => $order])
        </div>
        <div class="col-12 col-lg-4">
            @include('order.partials.groups', [
                'order' => $order,
                'readonly' => $order->isActive() === false,
            ])

            @php

            $show_alert = false;

            if ($order->aggregate->isActive()) {
                foreach ($order->involvedModifiers(true) as $modifier) {
                    if ($modifier->isTrasversal()) {
                        $show_alert = true;
                        break;
                    }
                }
            }

            @endphp

            @include('commons.modifications', [
                'obj' => $order,
                'skip_void' => true,
                'suggestion' => $show_alert ? __('orders.help.modifiers_notice') : '',
            ])

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('supplier.movements', $order->supplier))
                @include('commons.movementfield', [
                    'obj' => $order->payment,
                    'name' => 'payment_id',
                    'label' => __('generic.payment'),
                    'default' => \App\Movement::generate('order-payment', $currentgas, $order, $order->fullSupplierValue($summary, $shipped_modifiers) ?? 0),
                    'to_modal' => [
                        'amount_editable' => true,
                        'extra' => [
                            'reload-loadable' => '#order-list',
                        ],
                    ],
                    'help_popover' => __('orders.help.payment'),
                ])
            @else
                @include('commons.staticmovementfield', [
                    'obj' => $order->payment,
                    'label' => __('generic.payment'),
                ])
            @endif
        </div>
        <div class="col-12 col-lg-4">
            @include('order.files', ['order' => $order])
        </div>
    </div>

    <hr>

    @include('order.summary', [
        'order' => $order,
        'master_summary' => $master_summary,
        'shipped_modifiers' => $shipped_modifiers,
    ])

    @include('order.annotations', [
        'order' => $order
    ])
</x-larastrap::mform>

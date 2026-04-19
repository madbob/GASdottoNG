<x-ls::modal id="advancedFunctions">
    <x-ls::card header="generic.export">
        <p>
            {!! __('texts.orders.help.advanced_export') !!}
        </p>

        <x-ls::downloading tlabel="generic.export" :data-link="$order->exportableURL()" />
    </x-ls::card>

    <x-ls::card header="generic.do_duplicate">
        <p>
            {!! __('texts.orders.help.advanced_duplicate') !!}
        </p>

        @php

        $buttons = [
            [
                'tlabel' => 'orders.duplicate.simple',
                'attributes' => [
                    'type' => 'submit',
                    'name' => 'action',
                    'value' => 'simple'
                ]
            ],
            [
                'tlabel' => 'orders.duplicate.full',
                'attributes' => [
                    'type' => 'submit',
                    'name' => 'action',
                    'value' => 'full'
                ]
            ],
        ];

        @endphp

        <x-ls::iform :action="route('orders.duplicate', $order->id)" :buttons="$buttons" keep_buttons>
            <input type="hidden" name="close-modal" value="1">
            <input type="hidden" name="update-list" value="order-list">
            <x-larastrap::datepicker name="start" tlabel="orders.dates.start" required />
            <x-larastrap::datepicker name="end" tlabel="orders.dates.end" required :attributes="['data-enforce-after' => '.date[name=start]']" tpophelp="orders.help.end" />
            <x-larastrap::datepicker name="shipping" tlabel="orders.dates.shipping" :attributes="['data-enforce-after' => '.date[name=end]']" />
            <x-ls::orderstatus />
        </x-ls::iform>
    </x-ls::card>
</x-ls::modal>

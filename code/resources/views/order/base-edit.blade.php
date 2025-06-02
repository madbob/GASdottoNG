@php
$suppliers = $currentuser->targetsByAction('supplier.orders');
@endphp

@if($currentuser->gas->hasFeature('auto_aggregates'))
    <x-larastrap::field tlabel="orders.supplier" :pophelp="__('orders.help.supplier_multi_select', ['theshold' => aggregatesConvenienceLimit()])">
        @include('commons.manyrows', [
            'contents' => $order ? collect([$order]) : new Illuminate\Support\Collection(),
            'new_label' => __('generic.add_new'),
            'columns' => [
                [
                    'label' => __('orders.supplier'),
                    'field' => 'supplier',
                    'type' => 'select-model',
                    'extra' => [
                        'options' => $suppliers
                    ]
                ],
            ]
        ])
    </x-larastrap::field>
@else
    <x-larastrap::select-model name="supplier" tlabel="orders.supplier" :options="$suppliers" required />
@endif

<x-larastrap::textarea name="comment" tlabel="generic.comment" maxlength="190" rows="2" :pophelp="__('orders.help.comment', ['limit' => longCommentLimit()])" />
<x-larastrap::datepicker name="start" tlabel="orders.dates.start" defaults_now="true" required tpophelp="orders.help.start" />
<x-larastrap::datepicker name="end" tlabel="orders.dates.end" defaults_now="true" required data-enforce-after=".date[name=start]" tpophelp="orders.help.end" />
<x-larastrap::datepicker name="shipping" tlabel="orders.dates.shipping" defaults_now="true" required data-enforce-after=".date[name=end]" />

<x-larastrap::field>
    @if(empty($suppliers) == false)
        <div class="supplier-future-dates">
            @include('dates.list', ['dates' => App\Supplier::find(array_values($suppliers)[0]->id)->calendarDates])
        </div>
    @endif
</x-larastrap::field>

@include('order.partials.groups', ['order' => null, 'readonly' => false])
@include('commons.orderstatus', ['order' => $order])

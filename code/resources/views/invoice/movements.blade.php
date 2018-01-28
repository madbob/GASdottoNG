<div class="wizard_page">
    <form class="form-horizontal" method="POST" action="{{ url('invoices/wire/save/' . $invoice->id) }}" data-toggle="validator">
        @foreach($orders as $order)
            @include('commons.hiddenfield', ['obj' => $order, 'name' => 'id', 'prefix' => 'order_', 'postfix' => '[]'])
        @endforeach

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    @include('commons.hiddenfield', [
                        'obj' => $movements[0],
                        'name' => 'amount',
                        'postfix' => '[]'
                    ])
                    @include('commons.staticpricefield', [
                        'obj' => $movements[0],
                        'name' => 'amount',
                        'label' => 'Valore'
                    ])

                    @include('commons.hiddenfield', [
                        'obj' => $movements[0],
                        'name' => 'type',
                        'postfix' => '[]'
                    ])

                    @include('commons.staticenumfield', [
                        'obj' => $movements[0],
                        'name' => 'type',
                        'label' => 'Tipo',
                        'values' => [
                            [
                                'value' => 'invoice-payment',
                                'label' => App\MovementType::types('invoice-payment')->name
                            ]
                        ]
                    ])

                    @include('commons.textarea', [
                        'obj' => $movements[0],
                        'name' => 'notes',
                        'label' => 'Note',
                        'postfix' => '[]'
                    ])

                    @if(count($movements) > 1)
                        <hr>

                        @include('commons.hiddenfield', [
                            'obj' => $movements[1],
                            'name' => 'amount',
                            'postfix' => '[]'
                        ])

                        @include('commons.staticpricefield', [
                            'obj' => $movements[1],
                            'name' => 'amount',
                            'label' => 'Valore'
                        ])

                        @include('commons.selectenumfield', [
                            'obj' => $movements[1],
                            'name' => 'type',
                            'postfix' => '[]',
                            'label' => 'Tipo',
                            'values' => $alternative_types
                        ])

                        @include('commons.textarea', [
                            'obj' => $movements[1],
                            'name' => 'notes',
                            'label' => 'Note',
                            'postfix' => '[]'
                        ])
                    @endif
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
            <button type="submit" class="btn btn-success reloader" data-reload-target="#invoice-list">{{ _i('Salva') }}</button>
        </div>
    </form>
</div>

@extends('app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#movements-tab" aria-controls="movements-tab" role="tab" data-toggle="tab">{{ _i('Movimenti') }}</a></li>
            @can('movements.types', $currentgas)
                <li role="presentation"><a href="#movements-types-tab" aria-controls="movements-types-tab" role="tab" data-toggle="tab">{{ _i('Tipi Movimenti') }}</a></li>
            @endcan
            <li role="presentation"><a href="#invoices-tab" aria-controls="invoices-tab" role="tab" data-toggle="tab">{{ _i('Fatture') }}</a></li>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="movements-tab">
                <div class="row">
                    <div class="col-md-12">
                        @can('movements.admin', $currentgas)
                            @include('commons.addingbutton', [
                                'typename' => 'movement',
                                'typename_readable' => _i('Movimento'),
                                'dynamic_url' => route('movements.create')
                            ])

                            @include('commons.importcsv', [
                                'modal_id' => 'importCSVmovements',
                                'import_target' => 'movements'
                            ])

                            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#creditsStatus">{{ _i('Stato Crediti') }}</button>
                            <div class="modal fade dynamic-contents" id="creditsStatus" tabindex="-1" data-contents-url="{{ url('movements/showcredits') }}">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <div class="clearfix"></div>
                    <hr/>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-horizontal form-filler" data-action="{{ route('movements.index') }}" data-toggle="validator" data-fill-target="#movements-in-range">
                            @include('commons.genericdaterange', [
                                'start_date' => strtotime('-1 weeks'),
                            ])
                            @include('commons.selectmovementtypefield', ['show_all' => true])
                            @include('commons.radios', [
                                'name' => 'method',
                                'label' => _i('Pagamento'),
                                'values' => ['all' => (object)['name' => _i('Tutti'), 'checked' => true]] + App\MovementType::payments()
                            ])
                            @include('commons.selectobjfield', [
                                'obj' => null,
                                'name' => 'user_id',
                                'label' => _i('Utente'),
                                'objects' => $currentgas->users,
                                'extra_selection' => [
                                    '0' => _i('Nessuno')
                                ]
                            ])
                            @include('commons.selectobjfield', [
                                'obj' => null,
                                'name' => 'supplier_id',
                                'label' => _i('Fornitore'),
                                'objects' => $currentgas->suppliers,
                                'extra_selection' => [
                                    '0' => _i('Nessuno')
                                ]
                            ])
                            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountstart', 'label' => _i('Importo Minimo'), 'is_price' => true])
                            @include('commons.decimalfield', ['obj' => null, 'name' => 'amountend', 'label' => _i('Importo Massimo'), 'is_price' => true])

                            <div class="form-group">
                                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                                    <button type="submit" class="btn btn-success">{{ _i('Ricerca') }}</button>
                                    <a href="{{ url('movements?format=csv') }}" class="btn btn-default form-filler-download">{{ _i('Esporta CSV') }}</a>
                                    <a href="{{ url('movements?format=pdf') }}" class="btn btn-default form-filler-download">{{ _i('Esporta PDF') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-md-offset-2 current-balance">
                        @include('movement.status', ['obj' => $currentgas])
                    </div>
                </div>

                <hr/>

                <div class="row">
                    <div class="col-md-12" id="movements-in-range">
                        @include('movement.list', ['movements' => $movements])
                    </div>
                </div>
            </div>

            @can('movements.types', $currentgas)
                <div role="tabpanel" class="tab-pane" id="movements-types-tab">
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.addingbutton', [
                                'template' => 'movementtypes.base-edit',
                                'typename' => 'movementtype',
                                'typename_readable' => _i('Tipo Movimento'),
                                'targeturl' => 'movtypes'
                            ])
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <hr/>

                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.loadablelist', [
                                'identifier' => 'movementtype-list',
                                'items' => $types,
                            ])
                        </div>
                    </div>
                </div>
            @endcan

            <div role="tabpanel" class="tab-pane" id="invoices-tab">
                @can('movements.admin', $currentgas)
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.addingbutton', [
                                'template' => 'invoice.base-edit',
                                'typename' => 'invoice',
                                'typename_readable' => _i('Fattura'),
                                'button_label' => _i('Carica Nuova Fattura'),
                                'targeturl' => 'invoices'
                            ])
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <hr/>
                @endcan

                <div class="row">
                    <div class="col-md-12">
                        @include('commons.loadablelist', [
                            'identifier' => 'invoice-list',
                            'items' => $invoices,
                            'legend' => (object)[
                                'class' => 'Invoice'
                            ],
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('commons.deleteconfirm', [
    'url' => 'movements',
    'password_protected' => true,
    'extra' => [
        'close-all-modal' => '1',
        'post-saved-function' => ['refreshFilter', 'refreshBalanceView']
    ]
])

@include('commons.passwordmodal')

@endsection

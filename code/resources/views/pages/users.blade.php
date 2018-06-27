@extends('app')

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('users.admin', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'user.base-edit',
                'typename' => 'user',
                'typename_readable' => _i('Utente'),
                'targeturl' => 'users'
            ])

            @include('commons.importcsv', [
                'modal_id' => 'importCSVusers',
                'import_target' => 'users'
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#exportCSVusers">{{ _i('Esporta CSV') }}</button>
            <div class="modal fade close-on-submit" id="exportCSVusers" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-extra-lg" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal direct-submit" method="GET" action="{{ url('users/export') }}" data-toggle="validator" novalidate>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Esporta CSV') }}</h4>
                            </div>
                            <div class="modal-body">
                                @include('commons.checkboxes', [
                                    'name' => 'fields',
                                    'label' => _i('Colonne'),
                                    'labelsize' => 2,
                                    'fieldsize' => 10,
                                    'values' => App\User::formattableColumns()
                                ])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                <button type="submit" class="btn btn-success">{{ _i('Download') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
</div>

<div class="clearfix"></div>
<hr/>

<div class="row">
    <div class="col-md-12">
        @can('users.admin', $currentgas)
            @include('commons.loadablelist', [
                'identifier' => 'user-list',
                'items' => $users,
                'legend' => (object)[
                    'class' => 'User'
                ],
                'filters' => [
                    'deleted_at' => (object)[
                        'icon' => 'inbox',
                        'label' => _i('Sospesi e Cessati'),
                        'value' => null
                    ]
                ]
            ])
        @else
            @include('commons.loadablelist', [
                'identifier' => 'user-list',
                'items' => $users,
                'legend' => (object)[
                    'class' => 'User'
                ]
            ])
        @endif
    </div>
</div>

@endsection

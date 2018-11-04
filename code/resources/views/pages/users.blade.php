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

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#exportCSVusers">{{ _i('Esporta CSV') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
            <div class="modal fade close-on-submit" id="exportCSVusers" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-extra-lg" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal" method="GET" data-toggle="validator" novalidate>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Esporta CSV') }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>
                                    {{ _i("Verranno esportati gli utenti attualmente filtrati nella lista principale, in funzione del loro stato e del loro ruolo.") }}
                                </p>
                                <p>
                                    {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                                </p>

                                <hr/>

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
                                <button class="btn btn-success export-custom-list" data-export-url="{{ url('users/export') }}" data-target="#user-list">{{ _i('Download') }}</button>
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

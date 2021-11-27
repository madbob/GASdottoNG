@extends('app')

@section('content')

@can('users.admin', $currentgas)
    <div class="row">
        <div class="col">
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

            <x-larastrap::mbutton :label="_i('Esporta CSV')" triggers_modal="exportCSVusers" />
            <x-larastrap::modal id="exportCSVusers" :title="_i('Esporta CSV')" classes="close-on-submit">
                <x-larastrap::form method="GET" :buttons="[['label' => _i('Download'), 'classes' => 'export-custom-list', 'attributes' => ['data-export-url' => url('users/export'), 'data-target' => '#user-list']]]" label_width="2" input_width="10">
                    <p>
                        {{ _i("Verranno esportati gli utenti attualmente filtrati nella lista principale, in funzione del loro stato e del loro ruolo.") }}
                    </p>
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    <x-larastrap::structchecks name="fields" :label="_i('Colonne')" :options="App\Formatters\User::formattableColumns('export')" />
                </x-larastrap::form>
            </x-larastrap::modal>

            @if(Gate::check('users.admin', $currentgas) || Gate::check('users.movements', $currentgas))
                @if($currentgas->getConfig('annual_fee_amount') != 0)
                    <x-larastrap::ambutton :label="_i('Stato Quote')" :attributes="['data-modal-url' => route('users.fees')]" />
                @endif
            @endif
        </div>
    </div>

    <div class="clearfix"></div>
    <hr/>
@endcan

<div class="row">
    <div class="col">
        @can('users.admin', $currentgas)
            @include('commons.loadablelist', [
                'identifier' => 'user-list',
                'items' => $users,
                'legend' => (object)[
                    'class' => 'User'
                ],
                'filters' => [
                    'deleted_at' => (object) [
                        'icon' => 'inbox',
                        'label' => _i('Cessati'),
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

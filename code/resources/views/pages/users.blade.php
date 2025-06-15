@extends('app')

@section('content')

@can('users.admin', $currentgas)
    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'user.base-edit',
                'typename' => 'user',
                'typename_readable' => __('texts.user.name'),
                'targeturl' => 'users'
            ])

            @include('commons.importcsv', [
                'modal_id' => 'importCSVusers',
                'import_target' => 'users'
            ])

            <x-larastrap::mbutton tlabel="generic.exports.csv" triggers_modal="exportCSVusers" />
            <x-larastrap::modal id="exportCSVusers" classes="close-on-submit">
                <x-larastrap::iform method="GET" :action="url('users/export')" :buttons="[['tlabel' => 'generic.download', 'type' => 'submit']]">
                    <input type="hidden" name="pre-saved-function" value="collectFilteredUsers">
                    <input type="hidden" name="collectFilteredUsers" value="#user-list">
                    <input type="hidden" name="pre-saved-function" value="formToDownload">

                    <p>{{ __('texts.export.help_csv_libreoffice') }}</p>

                    <hr/>

                    <x-larastrap::structchecks name="fields" tlabel="export.data.columns" :options="App\Formatters\User::formattableColumns('export')" />
                    <x-larastrap::radios name="exportables" tlabel="generic.export" :options="[
                        'all' => __('texts.generic.all'),
                        'selected' => __('texts.generic.only_selected')
                    ]" value="all" />
                </x-larastrap::iform>
            </x-larastrap::modal>

            @if(Gate::check('users.admin', $currentgas) || Gate::check('users.movements', $currentgas))
                @if($currentgas->getConfig('annual_fee_amount') != 0)
                    <x-larastrap::ambutton tlabel="user.fees_status" :attributes="['data-modal-url' => route('users.fees')]" />
                @endif
            @endif

            @if(Gate::check('users.admin', $currentgas) && App\Group::where('context', 'user')->count() > 0)
                <x-larastrap::ambutton tlabel="user.assign_aggregations" :data-modal-url="route('groups.matrix')" />
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
                    'class' => App\User::class
                ],
                'filters' => [
                    'deleted_at' => (object) [
                        'icon' => 'inbox',
                        'label' => __('texts.user.all_ceased'),
                        'value' => null
                    ]
                ]
            ])
        @else
            @include('commons.loadablelist', [
                'identifier' => 'user-list',
                'items' => $users,
                'legend' => (object)[
                    'class' => App\User::class
                ]
            ])
        @endif
    </div>
</div>

@endsection

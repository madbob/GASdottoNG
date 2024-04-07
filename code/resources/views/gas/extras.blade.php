<x-larastrap::accordion always_open="true">
    <x-larastrap::accordionitem :label="_i('Gruppi')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'groups.base-edit',
                    'typename' => 'group',
                    'typename_readable' => _i('Gruppo'),
                    'targeturl' => 'groups'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'group-list',
                    'items' => App\Group::orderBy('name', 'asc')->get(),
                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi sarà possibile aggregare gli utenti in molteplici gruppi, in modo da separare le prenotazioni, organizzare la logistica delle consegne, applicare modificatori speciali e molto altro.')
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('File Condivisi')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'attachment.base-edit',
                    'typename' => 'attachment',
                    'target_update' => 'attachment-list-' . $gas->id,
                    'typename_readable' => _i('File'),
                    'targeturl' => 'attachments',
                    'extra' => [
                        'target_type' => 'App\Gas',
                        'target_id' => $gas->id
                    ]
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'attachment-list-' . $gas->id,
                    'items' => $gas->attachments,
                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>I files qui aggiunti saranno accessibili a tutti gli utenti dalla dashboard: utile per condividere documenti di interesse comune.')
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('Aliquote IVA')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'vatrates.base-edit',
                    'typename' => 'vatrate',
                    'typename_readable' => _i('Aliquota IVA'),
                    'targeturl' => 'vatrates'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'vatrate-list',
                    'items' => App\VatRate::orderBy('name', 'asc')->get(),
                    'empty_message' => _i("Non ci sono elementi da visualizzare.<br/>Le aliquote potranno essere assegnate ai diversi prodotti nei listini dei fornitori, e vengono usate per scorporare automaticamente l'IVA dai totali delle fatture caricate in <strong>Contabilità -> Fatture</strong>.")
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('Modificatori')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'modifiertype.base-edit',
                    'typename' => 'modtype',
                    'typename_readable' => _i('Modificatore'),
                    'targeturl' => 'modtypes'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'modtype-list',
                    'items' => App\ModifierType::where('hidden', false)->orderBy('name', 'asc')->get(),
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label="_i('Log')">
        <?php $logs = App\InnerLog::orderBy('created_at', 'desc')->take(20)->get() ?>

        <div class="row">
            <div class="col">
                @if($logs->isEmpty())
                    <x-larastrap::suggestion>
                        {{ _i('Non ci sono log.') }}
                    </x-larastrap::suggestion>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="30%">{{ _i('Data') }}</th>
                                <th width="70%">{{ _i('Messaggio') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>{{ printableDate($log->created_at) }}</td>
                                    <td>{{ $log->message }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </x-larastrap::accordionitem>
</x-larastrap::accordion>

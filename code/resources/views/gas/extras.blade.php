<x-larastrap::accordion always_open="true">
    <x-larastrap::accordionitem :label_html="formatAccordionLabel('aggregations.all', 'card-checklist')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'groups.base-edit',
                    'typename' => 'group',
                    'typename_readable' => __('texts.aggregations.name'),
                    'targeturl' => 'groups'
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'group-list',
                    'items' => App\Group::orderBy('name', 'asc')->get(),
                    'empty_message' => __('texts.aggregations.empty_list'),
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label_html="formatAccordionLabel('generic.shared_files', 'share')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'attachment.base-edit',
                    'typename' => 'attachment',
                    'target_update' => 'attachment-list-' . $gas->id,
                    'typename_readable' => __('texts.generic.file'),
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
                    'empty_message' => __('texts.gas.help.empty_list_shared_files'),
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label_html="formatAccordionLabel('movements.vat_rates', 'bank')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'vatrates.base-edit',
                    'typename' => 'vatrate',
                    'typename_readable' => __('texts.products.vat_rate'),
                    'targeturl' => 'vatrates',
                    'autoread' => true,
                ])
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                @include('commons.loadablelist', [
                    'identifier' => 'vatrate-list',
                    'items' => App\VatRate::orderBy('name', 'asc')->get(),
                    'empty_message' => __('texts.movements.help.empty_list_vat_rates'),
                ])
            </div>
        </div>
    </x-larastrap::accordionitem>

    <x-larastrap::accordionitem :label_html="formatAccordionLabel('modifiers.all', 'arrow-down-up')">
        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'modifiertype.base-edit',
                    'typename' => 'modtype',
                    'typename_readable' => __('texts.modifiers.name'),
                    'targeturl' => 'modtypes',
                    'autoread' => true,
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

    <x-larastrap::accordionitem :label_html="formatAccordionLabel('generic.logs', 'list-columns')">
        <?php $logs = App\InnerLog::orderBy('created_at', 'desc')->take(20)->get() ?>

        <div class="row">
            <div class="col">
                @if($logs->isEmpty())
                    <x-larastrap::suggestion>
                        {{ __('texts.generic.empty_list') }}
                    </x-larastrap::suggestion>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" width="30%">{{ __('texts.generic.date') }}</th>
                                <th scope="col" width="70%">{{ __('texts.generic.message') }}</th>
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

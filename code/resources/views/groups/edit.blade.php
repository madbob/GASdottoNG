<div>
    <x-larastrap::mform :obj="$group" classes="form-horizontal main-form group-editor" method="PUT" :action="route('groups.update', $group->id)">
        <div class="row">
            <div class="col-md-12">
                @include('groups.base-edit')

                <x-larastrap::radios :label="_i('Contesto')" name="context" :options="['user' => _i('Utente'), 'booking' => _i('Prenotazione'), 'order' => _i('Ordine')]" classes="selective-display" :attributes="['data-target' => '.optional']" />

                <div class="optional" data-type="user">
                    <x-larastrap::radios :label="_i('Ogni Utente può stare in')" name="cardinality" :options="['single' => _i('un solo Cerchio'), 'many' => _i('diversi Cerchi')]" />
                    <x-larastrap::check :label="_i('Selezionabile dall\'Utente')" name="user_selectable" />
                    <x-larastrap::check :label="_i('Limita accesso agli Ordini')" name="filters_orders" />
                    <x-larastrap::check :label="_i('Visibile dall\'Utente')" name="visible" />
                </div>
            </div>
        </div>
        <hr>
    </x-larastrap::mform>

    <hr>

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'circles.base-edit',
                'typename' => 'circle',
                'typename_readable' => _i('Cerchio'),
                'targeturl' => 'circles',
                'target_update' => 'circle-list-' . $group->id,
                'extra' => [
                    'group_id' => $group->id,
                ]
            ])
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => 'circle-list-' . $group->id,
                'items' => $group->circles,
            ])
        </div>
    </div>
</div>

@stack('postponed')

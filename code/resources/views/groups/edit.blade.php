<div>
    <x-larastrap::mform :obj="$group" classes="form-horizontal main-form group-editor" method="PUT" :action="route('groups.update', $group->id)">
        <div class="row">
            <div class="col-md-12">
                @include('groups.base-edit')

                <x-larastrap::radios :label="_i('Contesto')" name="context" :options="['user' => _i('Utente'), 'booking' => _i('Prenotazione')]" classes="selective-display" :attributes="['data-target' => '.optional']" :pophelp="_i('<ul><li>Utente: i Gruppi di questa Aggregazione sono assegnabili a ciascun utente, a priori, e valgono per tutti gli Ordini. Utile per partizionare gli utenti.</li><li>Prenotazione: i Gruppi di questa Aggregazione sono assegnabili a ciascuna Prenotazione da parte degli utenti. Utile per gestire la logistica, ad esempio in caso di molteplici punti di consegna arbitrariamente selezionabili dagli utenti.</li></ul>')" />

                <div class="optional" data-type="user">
                    <x-larastrap::radios :label="_i('Ogni Utente può stare in')" name="cardinality" :options="['single' => _i('un solo Gruppo'), 'many' => _i('diversi Gruppi')]" />
                    <x-larastrap::check :label="_i('Selezionabile dall\'Utente')" name="user_selectable" />
                    <x-larastrap::check :label="_i('Limita accesso Ordini')" name="filters_orders" :pophelp="_i('Se selezionato, sarà possibile scegliere uno o più Gruppi di questa Aggregazione nel contesto di ogni Ordine. Così facendo, l\'Ordine stesso sarà accessibile solo agli utenti che sono stati assegnati ai Gruppi stessi.')" />
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
                'typename_readable' => _i('Gruppo'),
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

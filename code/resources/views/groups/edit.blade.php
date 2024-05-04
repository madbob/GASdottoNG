<div>
    <x-larastrap::mform :obj="$group" classes="form-horizontal main-form group-editor" method="PUT" :action="route('groups.update', $group->id)">
        <div class="row">
            <div class="col-md-12">
                @include('groups.base-edit')

                <x-larastrap::radios :label="_i('Contesto')" name="context" :options="['user' => _i('Utente'), 'booking' => _i('Prenotazione'), 'order' => _i('Ordine')]" classes="selective-display" :attributes="['data-target' => '.optional']" :pophelp="_i('<ul><li>Utente: il Gruppo è assegnabile a ciascun utente, a priori, e vale per tutti gli Ordini. Utile per partizionare gli utenti.</li><li>Prenotazione: il Gruppo è assegnabile a ciascuna Prenotazione da parte degli utenti. Utile per gestire la logistica, in caso di molteplici punti di consegna arbitrariamente selezionabili dagli utenti.</li><li>Ordine: il Gruppo è assegnabile a ciascun Ordine, e viene mostrato nel pannello di Prenotazione (ma non può essere selezionato dagli utenti). Utile per veicolare informazioni, ad esempio relative al luogo di consegna o più in generale per etichettare gli Ordini.</li></ul>')" />

                <div class="optional" data-type="user">
                    <x-larastrap::radios :label="_i('Ogni Utente può stare in')" name="cardinality" :options="['single' => _i('un solo Cerchio'), 'many' => _i('diversi Cerchi')]" />
                    <x-larastrap::check :label="_i('Selezionabile dall\'Utente')" name="user_selectable" />
                    <x-larastrap::check :label="_i('Limita accesso Ordini')" name="filters_orders" :pophelp="_i('Se selezionato, sarà possibile scegliere uno o più Cerchie di questo Gruppo nel contesto di ogni Ordine. Così facendo, l\'Ordine stesso sarà accessibile solo agli utenti che sono stati assegnati alle Cerchie stesse.')" />
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

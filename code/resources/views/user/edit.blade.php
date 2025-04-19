<?php

if (isset($readonly) && $readonly) {
    $admin_editable = $editable = $personal_details = false;
}
else {
    $readonly = false;
    $admin_editable = $currentuser->can('users.admin', $currentgas);
    $editable = ($admin_editable || ($currentuser->id == $user->id && $currentuser->can('users.self', $currentgas)) || $user->parent_id == $currentuser->id);
    $personal_details = ($currentuser->id == $user->id);
}

$display_page = $display_page ?? false;
$has_accounting = ($admin_editable || $currentuser->id == $user->id || $currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas)) && ($user->isFriend() == false && someoneCan('movements.admin', $user->gas));
$has_stats = $has_accounting;
$has_bookings = ($currentuser->id == $user->id);
$has_friends = $editable && $user->can('users.subusers', $user->gas);
$has_notifications = $user->isFriend() == false && $editable && ($currentgas->getConfig('notify_all_new_orders') == false);

$friend_admin_buttons = [];
if ($user->isFriend() && $admin_editable) {
    $friend_admin_buttons = [
        [
            'label' => _i('Modifica Amico'),
            'classes' => ['float-start', 'prevent-default', 'me-2'],
            'attributes' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#change_friend_' . $user->id]
        ]
    ];
}

?>

<x-larastrap::tabs>
    <x-larastrap::tabpane :id="sprintf('profile-%s', sanitizeId($user->id))" label="{{ _i('Anagrafica') }}" active="true" classes="mb-2" icon="bi-person">
        @if($admin_editable)
            @if($user->pending)
                <div class="alert alert-warning float-start w-100 mb-3">
                    <div class="float-start d-inline-block">
                        {{ _i('Questo utente è in attesa di approvazione!') }}
                    </div>

                    <x-larastrap::iform :action="route('users.revisioned', $user->id)" :buttons="[['label' => _i('Approva'), 'color' => 'success']]" classes="float-end ms-2">
                        <x-larastrap::hidden name="post-saved-function" value="handleUserApproval" />
                        <x-larastrap::hidden name="action" value="approve" />
                    </x-larastrap::iform>

                    <x-larastrap::iform :action="route('users.revisioned', $user->id)" :buttons="[['label' => _i('Non Approvare ed Elimina'), 'color' => 'danger']]" classes="float-end">
                        <x-larastrap::hidden name="post-saved-function" value="handleUserApproval" />
                        <x-larastrap::hidden name="action" value="noapprove" />
                    </x-larastrap::iform>
                </div>
            @endif
        @endif

        <x-larastrap::mform :obj="$user" method="PUT" :action="route('users.update', $user->id)" :classes="$display_page ? 'inner-form' : ''" :nodelete="$display_page || $user->isFriend() == false" :nosave="$readonly" :other_buttons="$friend_admin_buttons">
            <div class="row">
                <div class="col-12 col-md-6">
                    @if($user->isFriend() == false)
                        @if($editable)
                            <x-larastrap::username name="username" :label="_i('Username')" required />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" />
                            @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            <x-larastrap::text name="birthplace" :label="_i('Luogo di Nascita')" />
                            <x-larastrap::datepicker name="birthday" :label="_i('Data di Nascita')" />
                            <x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" />
                            <x-larastrap::text name="family_members" :label="_i('Persone in Famiglia')" />
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            <x-larastrap::username name="username" :label="_i('Username')" readonly disabled />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" readonly disabled />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" readonly disabled />

                            @if($personal_details)
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                                <x-larastrap::datepicker name="birthday" :label="_i('Data di Nascita')" readonly disabled />
                                <x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" readonly disabled />
                            @endif

                            @include('commons.staticcontactswidget', ['obj' => $user])
                        @endif
                    @else
                        @if($editable)
                            <x-larastrap::username name="username" :label="_i('Username')" />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" />
                            @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            <x-larastrap::username name="username" :label="_i('Username')" readonly disabled />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" readonly disabled />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" readonly disabled />

                            @if($personal_details)
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            @endif

                            @include('commons.staticcontactswidget', ['obj' => $user])
                        @endif
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    @if($user->isFriend() == false)
                        @if($editable)
                            @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                        @else
                            @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                        @endif

                        @if($admin_editable)
                            <x-larastrap::datepicker name="member_since" :label="_i('Membro da')" />
                            <x-larastrap::text name="card_number" :label="_i('Numero Tessera')" />
                        @else
                            <x-larastrap::datepicker name="member_since" :label="_i('Membro da')" readonly disabled />
                            <x-larastrap::text name="card_number" :label="_i('Numero Tessera')" readonly disabled />
                        @endif

                        @if($editable || $personal_details)
                            @include('user.movements', ['editable' => $admin_editable])

                            <x-larastrap::datepicker name="last_login" :label="_i('Ultimo Accesso')" readonly disabled />
                            <x-larastrap::datepicker name="last_booking" :label="_i('Ultima Prenotazione')" readonly disabled />

                            @foreach($user->eligibleGroups() as $ug)
                                @if($admin_editable || $ug->user_selectable)
                                    <x-larastrap::hidden name="groups[]" :value="$ug->id" />
                                    <x-dynamic-component :component="sprintf('larastrap::%s', $ug->cardinality == 'single' ? 'radiolist-model' : 'checklist-model')" :params="['name' => 'circles', 'npostfix' => sprintf('__%s__%s[]', $user->id, $ug->id), 'label' => $ug->name, 'options' => $ug->circles]" />
                                @else
                                    <x-dynamic-component :component="sprintf('larastrap::%s', $ug->cardinality == 'single' ? 'radiolist-model' : 'checklist-model')" :params="['name' => 'circles', 'npostfix' => sprintf('__%s__%s[]', $user->id, $ug->id), 'label' => $ug->name, 'options' => $ug->circles]" disabled readonly />
                                @endif
                            @endforeach
                        @endif

                        @if($admin_editable)
                            @include('commons.statusfield', ['target' => $user])
                            <x-larastrap::radios name="payment_method_id" :label="_i('Modalità Pagamento')" :options="paymentsSimple()" />

                            @if($user->gas->hasFeature('rid'))
                                <x-larastrap::field :label="_i('Configurazione SEPA')" :pophelp="_i('Specifica qui i parametri per la generazione dei RID per questo utente. Per gli utenti per i quali questi campi non sono stati compilati non sarà possibile generare alcun RID.')">
                                    <x-larastrap::text name="rid->iban" :label="_i('IBAN')" squeeze="true" :value="$user->rid['iban'] ?? ''" :placeholder="_i('IBAN')" />
                                    <x-larastrap::text name="rid->id" :label="_i('Identificativo Mandato SEPA')" squeeze="true" :value="$user->rid['id'] ?? ''" :placeholder="_i('Identificativo Mandato SEPA')" />
                                    <x-larastrap::datepicker name="rid->date" :label="_i('Data Mandato SEPA')" squeeze="true" :value="$user->rid['date'] ?? ''" />
                                </x-larastrap::field>
                            @endif
                        @endif

                        @include('commons.permissionsviewer', ['object' => $user, 'editable' => $admin_editable])
                    @else
                        <x-larastrap::datepicker name="member_since" :label="_i('Membro da')" readonly disabled />
                        <x-larastrap::datepicker name="last_login" :label="_i('Ultimo Accesso')" readonly disabled />
                        <x-larastrap::datepicker name="last_booking" :label="_i('Ultima Prenotazione')" readonly disabled />
                    @endif
                </div>
            </div>

            <hr/>
        </x-larastrap::mform>

        @if($user->isFriend() == false && $currentuser->id === $user->id && $currentuser->can('users.selfdestroy'))
            @php
            $removeModalId = sprintf('remove-account-%s', sanitizeId($user->id));
            @endphp

            <x-larastrap::link color="danger" classes="float-end mt-2" :triggers_modal="$removeModalId" :label="_i('Elimina profilo')" />

            <x-larastrap::modal :id="$removeModalId">
                <x-larastrap::iform method="DELETE" :action="route('users.destroy', $user->id)" id="user-destroy-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'label' => _i('Elimina profilo')]]">
                    <p>
                        {{ _i('Vuoi davvero eliminare questo account? Tutti i dati personali saranno anonimizzati, benché sarà preservato lo storico delle prenotazioni.') }}
                    </p>

                    @if($user->currentBalanceAmount() != 0)
                        <p>
                            {{ _i("Prima di procedere, è consigliato contattare i referenti del GAS per regolare i conti sul credito.") }}
                        </p>
                    @endif
                    <input type="hidden" name="pre-saved-function" value="passwordProtected">
                </x-larastrap::iform>
            </x-larastrap::modal>
        @endif

        @if($user->isFriend() && $admin_editable)
            @push('postponed')
                <x-larastrap::modal :id="sprintf('change_friend_%s', $user->id)">
                    <x-larastrap::accordion>
                        <x-larastrap::accordionitem :label="_i('Promuovi a utente regolare')" active="false">
                            <x-larastrap::mform :action="route('users.promote', $user->id)" keep_buttons="true" nodelete="true">
                                <x-larastrap::hidden name="close-modal" value="1" />
                                <x-larastrap::hidden name="reload-portion" :value="sprintf('#friends-tab-%s', $user->parent_id)" />
                                <x-larastrap::hidden name="append-list" value="user-list" />

                                <p>
                                    {{ _i('Cliccando "Salva", questo utente diventerà un utente regolare. Gli sarà assegnato il ruolo %s, avrà una propria contabilità, e non potrà più essere amministrato da %s. Sarà preservato lo storico delle sue prenotazioni, ma tutti i suoi pagamenti pregressi resteranno addebitati a %s.', roleByIdentifier('user')->name, $user->parent->printableName(), $user->parent->printableName()) }}
                                </p>

                                @if(blank($user->email))
                                    <hr>
                                    <x-larastrap::email :label="_i('E-Mail')" name="email" :help="_i('È necessario specificare almeno un indirizzo email di contatto del nuovo utente')" required />
                                @endif
                            </x-larastrap::mform>
                        </x-larastrap::accordionitem>
                        <x-larastrap::accordionitem :label="_i('Cambia assegnazione')" active="false">
                            <x-larastrap::mform :action="route('users.reassign', $user->id)" keep_buttons="true" nodelete="true">
                                <x-larastrap::hidden name="close-modal" value="1" />
                                <x-larastrap::hidden name="reload-portion" :value="sprintf('#friends-tab-%s', $user->parent_id)" />

                                <p>
                                    {{ _i('Da qui è possibile riassegnare un amico ad un altro utente. Tutti i pagamenti pregressi resteranno addebitati a %s.', $user->parent->printableName()) }}
                                </p>

								<x-larastrap::select-model :label="_i('Nuovo assegnatario')" name="parent_id" :options="App\User::where('id', '!=', $user->parent_id)->with(['gas'])->topLevel()->sorted()->get()->filter(fn($u) => $u->can('users.subusers', $u->gas))" />
                            </x-larastrap::mform>
                        </x-larastrap::accordionitem>
                    </x-larastrap::accordion>
                </x-larastrap::modal>
            @endpush
        @endif
    </x-larastrap::tabpane>

    @if($has_accounting)
        <x-larastrap::remotetabpane :id="sprintf('accounting-%s', sanitizeId($user->id))" :label="_i('Contabilità')" :button_attributes="['data-tab-url' => route('users.accounting', $user->id)]" icon="bi-piggy-bank">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_accounting && $user->gas->hasFeature('extra_invoicing'))
        <x-larastrap::remotetabpane :id="sprintf('receipts-%s', sanitizeId($user->id))" :label="_i('Ricevute')" :button_attributes="['data-tab-url' => route('receipts.index', ['user_id' => $user->id])]" icon="bi-graph-up">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_bookings)
        <x-larastrap::remotetabpane :id="sprintf('bookings-%s', sanitizeId($user->id))" :label="_i('Prenotazioni')" :button_attributes="['data-tab-url' => route('users.bookings', $user->id)]" icon="bi-list-task">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_stats)
        <x-larastrap::remotetabpane :id="sprintf('stats-%s', sanitizeId($user->id))" :label="_i('Statistiche')" :button_attributes="['data-tab-url' => route('users.stats', $user->id)]" icon="bi-graph-up">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_friends)
        <x-larastrap::remotetabpane :id="sprintf('friends-%s', sanitizeId($user->id))" :label="_i('Amici')" :button_attributes="['id' => sprintf('friends-tab-%s', sanitizeId($user->id)), 'data-tab-url' => route('users.friends', $user->id)]" icon="bi-person-add">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_notifications)
        <x-larastrap::tabpane :id="sprintf('notifications-%s', sanitizeId($user->id))" label="{{ _i('Notifiche') }}" icon="bi-bell">
            <form class="form-horizontal inner-form" method="POST" action="{{ route('users.notifications', $user->id) }}">
                <div class="row">
                    <div class="col-md-4">
                        <p>
                            {{ _i("Seleziona i fornitori per i quali vuoi ricevere una notifica all'apertura di nuovi ordini.") }}
                        </p>
                        <ul class="list-group">
                            @foreach(App\Supplier::orderBy('name', 'asc')->get() as $supplier)
                                <li class="list-group-item">
                                    {{ $supplier->name }}
                                    <span class="float-end">
                                        <x-larastrap::scheck name="suppliers[]" :value="$supplier->id" :checked="$user->suppliers->where('id', $supplier->id)->first() != null" />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col">
                        <div class="btn-group float-end main-form-buttons" role="group">
                            <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>

<div class="postponed">
    @stack('postponed')
</div>

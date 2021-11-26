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
$has_accounting = ($admin_editable || $currentuser->id == $user->id) && ($user->isFriend() == false && someoneCan('movements.admin', $user->gas));
$has_bookings = ($currentuser->id == $user->id);
$has_friends = $editable && $user->can('users.subusers');
$has_notifications = $user->isFriend() == false && $editable && ($currentgas->getConfig('notify_all_new_orders') == false);

?>

<x-larastrap::tabs>
    <x-larastrap::tabpane :id="sprintf('profile-%s', sanitizeId($user->id))" label="{{ _i('Anagrafica') }}" active="true" classes="mb-2">
        <x-larastrap::mform :obj="$user" method="PUT" :action="route('users.update', $user->id)" :classes="$display_page ? 'inner-form' : ''" :nodelete="$display_page || $user->isFriend() == false" :nosave="$readonly">
            <div class="row">
                <div class="col-12 col-md-6">
                    @if($user->isFriend() == false)
                        @if($editable)
                            <x-larastrap::text name="username" :label="_i('Username')" required />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" />
                            @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            <x-larastrap::datepicker name="birthday" :label="_i('Data di Nascita')" />
                            <x-larastrap::text name="taxcode" :label="_i('Codice Fiscale')" />
                            <x-larastrap::text name="family_members" :label="_i('Persone in Famiglia')" />
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            <x-larastrap::text name="username" :label="_i('Username')" readonly disabled />
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
                            <x-larastrap::text name="username" :label="_i('Username')" />
                            <x-larastrap::text name="firstname" :label="_i('Nome')" />
                            <x-larastrap::text name="lastname" :label="_i('Cognome')" />
                            @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            <x-larastrap::text name="username" :label="_i('Username')" readonly disabled />
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

                            @if($currentgas->hasFeature('shipping_places'))
                                <x-larastrap::selectobj name="preferred_delivery_id" :label="_i('Luogo di Consegna')" :options="$currentgas->deliveries" :extraitem="_i('Nessuno')" :pophelp="_i('Dove l\'utente preferisce avere i propri prodotti recapitati. Permette di organizzare le consegne in luoghi diversi.')" />
                            @endif
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

                        <hr/>

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
    </x-larastrap::tabpane>

    @if($has_accounting)
        <x-larastrap::remotetabpane :label="_i('Contabilità')" :button_attributes="['data-tab-url' => route('users.accounting', $user->id)]">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_bookings)
        <x-larastrap::remotetabpane :label="_i('Prenotazioni')" :button_attributes="['data-tab-url' => route('users.bookings', $user->id)]">
        </x-larastrap::remotetabpane>
    @endif

    @if($has_friends)
        <x-larastrap::tabpane :id="sprintf('friends-%s', sanitizeId($user->id))" :label="_i('Amici')">
            <div class="row">
                <div class="col">
                    @include('commons.addingbutton', [
                        'user' => null,
                        'template' => 'friend.base-edit',
                        'typename' => 'friend',
                        'typename_readable' => _i('Amico'),
                        'targeturl' => 'friends',
                        'extra' => [
                            'creator_id' => $user->id,
                        ]
                    ])
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col">
                    @include('commons.loadablelist', [
                        'identifier' => 'friend-list',
                        'items' => $user->friends,
                        'empty_message' => _i('Aggiungi le informazioni relative agli amici per i quali vuoi creare delle sotto-prenotazioni. Ogni singola prenotazione sarà autonoma, ma trattata come una sola in fase di consegna. Ogni amico può anche avere delle proprie credenziali di accesso, per entrare in GASdotto e popolare da sé le proprie prenotazioni.'),
                        'url' => 'users'
                    ])
                </div>
            </div>
        </x-larastrap::tabpane>
    @endif

    @if($has_notifications)
        <x-larastrap::tabpane :id="sprintf('notifications-%s', sanitizeId($user->id))" label="{{ _i('Notifiche') }}">
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

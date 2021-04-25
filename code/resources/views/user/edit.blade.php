<?php

if (isset($readonly) && $readonly) {
    $admin_editable = $editable = $personal_details = false;
}
else {
    $admin_editable = $currentuser->can('users.admin', $currentgas);
    $editable = ($admin_editable || ($currentuser->id == $user->id && $currentuser->can('users.self', $currentgas)) || $user->parent_id == $currentuser->id);
    $personal_details = ($currentuser->id == $user->id);
}

$display_page = $display_page ?? false;
$has_accounting = $editable && ($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas));
$has_bookings = ($currentuser->id == $user->id);
$has_friends = $editable && $user->can('users.subusers');
$has_notifications = $user->isFriend() == false && $editable && ($currentgas->getConfig('notify_all_new_orders') == false);

?>

@if($editable)
    <?php $active_tab = $active_tab ?? 'profile' ?>

    @include('commons.tabs', ['active' => $active_tab, 'tabs' => [
        (object) [
            'label' => _i('Anagrafica'),
            'id' => 'profile'
        ],
        (object) [
            'label' => _i('Contabilità'),
            'id' => 'accounting',
            'enabled' => $has_accounting,
        ],
        (object) [
            'label' => _i('Prenotazioni'),
            'id' => 'bookings',
            'enabled' => $has_bookings,
        ],
        (object) [
            'label' => _i('Amici'),
            'id' => 'friends',
            'enabled' => $has_friends,
        ],
        (object) [
            'label' => _i('Notifiche'),
            'id' => 'notifications',
            'enabled' => $has_notifications,
        ],
    ]])
@else
    <?php $active_tab = 'profile' ?>
@endif

<div class="tab-content">
    <div role="tabpanel" class="tab-pane {{ $active_tab == 'profile' ? 'active' : '' }}" id="profile">
        <form class="form-horizontal main-form user-editor {{ $display_page ? 'inner-form' : '' }}" method="PUT" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data" autocomplete="off">
            <div class="row">
                <div class="col-md-6">
                    @if($user->isFriend() == false)
                        @if($editable)
                            @include('user.base-edit', ['user' => $user])
                            @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
                            @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
                            @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => _i('Persone in Famiglia')])
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'username', 'label' => _i('Username')])
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome')])
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome')])

                            @if($personal_details)
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
                                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
                            @endif

                            @include('commons.staticcontactswidget', ['obj' => $user])
                        @endif
                    @else
                        @if($editable)
                            @include('user.base-edit', ['user' => $user])
                            @include('commons.contactswidget', ['obj' => $user])
                        @else
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'username', 'label' => _i('Username')])
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome')])
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome')])

                            @if($personal_details)
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                            @endif

                            @include('commons.staticcontactswidget', ['obj' => $user])
                        @endif
                    @endif
                </div>
                <div class="col-md-6">
                    @if($user->isFriend() == false)
                        @if($editable)
                            @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                        @else
                            @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                        @endif

                        @if($admin_editable)
                            @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                            @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                        @else
                            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                        @endif

                        @if($editable || $personal_details)
                            @include('user.movements', ['editable' => $admin_editable])

                            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])
                            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_booking', 'label' => _i('Ultima Prenotazione')])

                            @if($currentgas->hasFeature('shipping_places'))
                                @include('commons.selectobjfield', [
                                    'obj' => $user,
                                    'name' => 'preferred_delivery_id',
                                    'objects' => $currentgas->deliveries,
                                    'label' => _i('Luogo di Consegna'),
                                    'help_popover' => _i("Dove l'utente preferisce avere i propri prodotti recapitati. Permette di organizzare le consegne in luoghi diversi."),
                                    'extra_selection' => [
                                        '0' => _i('Nessuno')
                                    ]
                                ])
                            @endif
                        @endif

                        @if($admin_editable)
                            @include('commons.statusfield', ['target' => $user])

                            <div class="form-group">
                                <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Modalità Pagamento') }}</label>

                                <div class="col-sm-{{ $fieldsize }}">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default {{ $user->payment_method->id == 'none' ? 'active' : '' }}">
                                            <input type="radio" name="payment_method_id" value="none" {{ $user->payment_method->id == 'none' ? 'checked' : '' }}> {{ _i('Non Specificato') }}
                                        </label>
                                        @foreach(App\MovementType::payments() as $payment_identifier => $payment_meta)
                                            <label class="btn btn-default {{ $user->payment_method->id == $payment_identifier ? 'active' : '' }}">
                                                <input type="radio" name="payment_method_id" value="{{ $payment_identifier }}" {{ $user->payment_method->id == $payment_identifier ? 'checked' : '' }}> {{ $payment_meta->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if($user->gas->hasFeature('rid'))
                                <div class="form-group">
                                    <label class="col-sm-{{ $labelsize }} control-label">
                                        @include('commons.helpbutton', ['help_popover' => _i("Specifica qui i parametri per la generazione dei RID per questo utente. Per gli utenti per i quali questi campi non sono stati compilati non sarà possibile generare alcun RID.")])
                                        {{ _i('Configurazione SEPA') }}
                                    </label>

                                    <div class="col-sm-{{ $fieldsize }}">
                                        @include('commons.textfield', ['obj' => $user, 'name' => 'rid->iban', 'label' => _i('IBAN'), 'squeeze' => true])

                                        <div class="form-group">
                                            <div class="col-sm-5">
                                                @include('commons.textfield', ['obj' => $user, 'name' => 'rid->id', 'label' => _i('Identificativo Mandato SEPA'), 'squeeze' => true])
                                            </div>
                                            <div class="col-sm-7">
                                                @include('commons.datefield', ['obj' => $user, 'name' => 'rid->date', 'label' => _i('Data Mandato SEPA'), 'squeeze' => true])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <hr/>
                        @include('commons.permissionsviewer', ['object' => $user, 'editable' => $admin_editable])
                    @else
                        @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                        @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])
                        @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_booking', 'label' => _i('Ultima Prenotazione')])
                    @endif
                </div>
            </div>

            @if($display_page)
                <div class="row">
                    <hr>
                    <div class="col-md-12">
                        <div class="btn-group pull-right main-form-buttons" role="group">
                            <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                        </div>
                    </div>
                </div>
            @else
                @include('commons.formbuttons', ['obj' => $user, 'no_delete' => $user->isFriend() == false])
            @endif
        </form>
    </div>

    @if($has_accounting)
        <div role="tabpanel" class="tab-pane {{ $active_tab == 'accounting' ? 'active' : '' }}" id="accounting">
            @if($user->gas->hasFeature('paypal'))
                <button type="button" class="btn btn-warning pull-right" data-toggle="modal" data-target="#paypalCredit">{{ _i('Ricarica Credito con PayPal') }}</button>

                <div class="modal fade" id="paypalCredit" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form class="form-horizontal direct-submit" method="POST" action="{{ route('payment.do') }}" data-toggle="validator">
                                @csrf

                                <input type="hidden" name="type" value="paypal">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">{{ _i('Ricarica Credito') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        {{ _i('Da qui puoi ricaricare il tuo credito utilizzando PayPal.') }}
                                    </p>
                                    <p>
                                        {{ _i('Specifica quanto vuoi versare ed eventuali note per gli amministratori, verrai rediretto sul sito PayPal dove dovrai autenticarti e confermare il versamento.') }}
                                    </p>
                                    <p>
                                        {{ _i('Eventuali commissioni sulla transazione saranno a tuo carico.') }}
                                    </p>

                                    @include('commons.decimalfield', ['obj' => null, 'name' => 'amount', 'label' => _i('Valore'), 'is_price' => true, 'mandatory' => true])
                                    @include('commons.textarea', ['obj' => null, 'name' => 'description', 'label' => _i('Descrizione')])
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                    <button type="submit" class="btn btn-success">{{ _i('Vai a PayPal') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <br>
            @endif

            @if($user->gas->hasFeature('satispay'))
                <button type="button" class="btn btn-warning pull-right" data-toggle="modal" data-target="#satispayCredit">{{ _i('Ricarica Credito con Satispay') }}</button>

                <div class="modal fade" id="satispayCredit" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form class="form-horizontal direct-submit" method="POST" action="{{ route('payment.do') }}" data-toggle="validator">
                                @csrf

                                <input type="hidden" name="type" value="satispay">

                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">{{ _i('Ricarica Credito') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        {{ _i('Da qui puoi ricaricare il tuo credito utilizzando Satispay.') }}
                                    </p>
                                    <p>
                                        {{ _i('Specifica quanto vuoi versare ed eventuali note per gli amministratori; riceverai una notifica sul tuo smartphone per confermare, entro 15 minuti, il versamento.') }}
                                    </p>

                                    @include('commons.textfield', ['obj' => $user, 'name' => 'mobile', 'label' => _i('Numero di Telefono')])
                                    @include('commons.decimalfield', ['obj' => null, 'name' => 'amount', 'label' => _i('Valore'), 'is_price' => true, 'mandatory' => true])
                                    @include('commons.textarea', ['obj' => null, 'name' => 'description', 'label' => _i('Descrizione')])
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                    <button type="submit" class="btn btn-success">{{ _i('Conferma con Satispay') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <br>
            @endif

            @include('movement.targetlist', ['target' => $user])
        </div>
    @endif

    @if($has_bookings)
        <div role="tabpanel" class="tab-pane {{ $active_tab == 'bookings' ? 'active' : '' }}" id="bookings">
            <br>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-horizontal form-filler" data-action="{{ route('users.orders', $user->id) }}" data-toggle="validator" data-fill-target="#user-booking-list">
                        @include('commons.selectobjfield', [
                            'obj' => null,
                            'name' => 'supplier_id',
                            'label' => _i('Fornitore'),
                            'mandatory' => true,
                            'objects' => $currentgas->suppliers,
                            'extra_selection' => [
                                '0' => _i('Tutti')
                            ]
                        ])

                        @include('commons.genericdaterange')

                        <div class="form-group">
                            <div class="col-md-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                                <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12" id="user-booking-list">
                    @include('commons.orderslist', ['orders' => $booked_orders ?? []])
                </div>
            </div>
        </div>
    @endif

    @if($has_friends)
        <div role="tabpanel" class="tab-pane {{ $active_tab == 'friends' ? 'active' : '' }}" id="friends">
            <div class="row">
                <div class="col-md-12">
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
                <div class="col-md-12">
                    @include('commons.loadablelist', [
                        'identifier' => 'friend-list',
                        'items' => $user->friends,
                        'empty_message' => _i('Aggiungi le informazioni relative agli amici per i quali vuoi creare delle sotto-prenotazioni. Ogni singola prenotazione sarà autonoma, ma trattata come una sola in fase di consegna. Ogni amico può anche avere delle proprie credenziali di accesso, per entrare in GASdotto e popolare da sé le proprie prenotazioni.'),
                        'url' => 'users'
                    ])
                </div>
            </div>
        </div>
    @endif

    @if($has_notifications)
        <div role="tabpanel" class="tab-pane {{ $active_tab == 'notifications' ? 'active' : '' }}" id="notifications">
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
                                    <span class="pull-right">
                                        <input name="suppliers[]" type="checkbox" value="{{ $supplier->id }}" data-toggle="toggle" data-size="mini" {{ $user->suppliers->where('id', $supplier->id)->first() != null ? 'checked' : '' }}>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right main-form-buttons" role="group">
                            <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

<div class="postponed">
    @stack('postponed')
</div>

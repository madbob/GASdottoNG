<?php

$is_me = $currentuser->id == $user->id;

$can_profile = true;
$can_accounting = ($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas) && ($is_me || $currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas)));
$can_bookings = $user->can('supplier.book') && ($is_me || $currentuser->can('supplier.orders', null) || $currentuser->can('supplier.shippings', null));
$can_friends = ($is_me && $user->can('users.subusers')) || $currentuser->can('users.adminsubusers');
$can_notifications = $currentgas->getConfig('notify_all_new_orders') == false && ($is_me || $currentuser->can('users.admin'));

$active_tab = $active_tab ?? 'profile';

?>

<div class="row">
    <div class="col-md-12">
        @include('commons.tabs', ['active' => $active_tab, 'tabs' => [
            (object) [
                'label' => _i('Anagrafica'),
                'id' => 'profile',
                'enabled' => $can_profile,
            ],
            (object) [
                'label' => _i('Contabilità'),
                'id' => 'accounting',
                'enabled' => $can_accounting,
            ],
            (object) [
                'label' => _i('Prenotazioni'),
                'id' => 'bookings',
                'enabled' => $can_bookings,
            ],
            (object) [
                'label' => _i('Amici'),
                'id' => 'friends',
                'enabled' => $can_friends,
            ],
            (object) [
                'label' => _i('Notifiche'),
                'id' => 'notifications',
                'enabled' => $can_notifications,
            ],
        ]])

        @if($can_profile)
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane {{ $active_tab == 'profile' ? 'active' : '' }}" id="profile">
                    <form class="form-horizontal inner-form user-editor" method="PUT" action="{{ route('users.update', $user->id) }}">
                        <div class="row">
                            <div class="col-md-6">
                                @if(($is_me && $currentuser->can('users.self', $currentgas)) || $currentuser->can('users.admin'))
                                    @include('user.base-edit', ['user' => $user])
                                    @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
                                    @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
                                    @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => _i('Persone in Famiglia')])
                                    @include('commons.contactswidget', ['obj' => $user])
                                @else
                                    @include('commons.staticstringfield', ['obj' => $user, 'name' => 'username', 'label' => _i('Username')])
                                    @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome')])
                                    @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome')])
                                    @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password')])
                                    @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
                                    @include('commons.staticstringfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
                                    @include('commons.staticcontactswidget', ['obj' => $user])
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($user->isFriend() == false)
                                    @if(($is_me && $currentuser->can('users.self', $currentgas)) || $currentuser->can('users.admin'))
                                        @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                                    @else
                                        @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                                    @endif

                                    @if($currentuser->can('users.admin', $currentgas))
                                        @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                                        @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                                    @else
                                        @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                                        @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
                                    @endif

                                    @include('user.movements', ['user' => $user, 'editable' => true])

                                    @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])
                                    @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_booking', 'label' => _i('Ultima Prenotazione')])

                                    @if($currentgas->hasFeature('shipping_places'))
                                        @include('commons.selectobjfield', [
                                            'obj' => $user,
                                            'name' => 'preferred_delivery_id',
                                            'objects' => $currentgas->deliveries,
                                            'label' => _i('Luogo di Consegna'),
                                            'extra_selection' => [
                                                '0' => _i('Nessuno')
                                            ]
                                        ])
                                    @endif

                                    <hr/>

                                    @include('commons.permissionsviewer', ['object' => $user, 'editable' => true])
                                @endif
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

            @if($can_accounting)
                <div role="tabpanel" class="tab-pane {{ $active_tab == 'accounting' ? 'active' : '' }}" id="accounting">
                    @if($currentgas->hasFeature('paypal'))
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

                    @if($currentgas->hasFeature('satispay'))
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

                                            @include('commons.textfield', ['obj' => $currentuser, 'name' => 'mobile', 'label' => _i('Numero di Telefono')])
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

            @if($can_bookings)
                <div role="tabpanel" class="tab-pane {{ $active_tab == 'bookings' ? 'active' : '' }}" id="bookings">
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-horizontal form-filler" data-action="{{ url('users/searchorders') }}" data-toggle="validator" data-fill-target="#user-booking-list">
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
                            @include('commons.orderslist', ['orders' => $booked_orders])
                        </div>
                    </div>
                </div>
            @endif

            @if($can_friends)
                <div role="tabpanel" class="tab-pane {{ $active_tab == 'friends' ? 'active' : '' }}" id="friends">
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.addingbutton', [
                                'user' => null,
                                'template' => 'friend.base-edit',
                                'typename' => 'friend',
                                'typename_readable' => _i('Amico'),
                                'targeturl' => 'friends'
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
                                'url' => 'friends'
                            ])
                        </div>
                    </div>
                </div>
            @endif

            @if($can_notifications)
                <div role="tabpanel" class="tab-pane {{ $active_tab == 'notifications' ? 'active' : '' }}" id="notifications">
                    <form class="form-horizontal inner-form" method="POST" action="{{ route('users.notifications') }}">
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
    </div>
</div>

@stack('postponed')

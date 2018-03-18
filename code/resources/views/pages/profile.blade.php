@extends('app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#profile" role="tab" data-toggle="tab">Anagrafica</a></li>

            @if($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas))
                <li role="presentation"><a href="#accounting" role="tab" data-toggle="tab">Contabilità</a></li>
            @endif

            @if($user->can('supplier.book'))
                <li role="presentation"><a href="#bookings" role="tab" data-toggle="tab">Prenotazioni</a></li>
            @endif

            @if($user->can('users.subusers'))
                <li role="presentation"><a href="#friends" role="tab" data-toggle="tab">Amici</a></li>
            @endif
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="profile">
                <form class="form-horizontal inner-form user-editor" method="PUT" action="{{ route('users.update', $user->id) }}">
                    <div class="row">
                        <div class="col-md-6">
                            @if($currentuser->can('users.self', $currentgas))
                                @include('user.base-edit', ['user' => $user])
                                @include('commons.contactswidget', ['obj' => $user])
                            @else
                                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'username', 'label' => _i('Username'), 'mandatory' => true])
                                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome'), 'mandatory' => true])
                                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome'), 'mandatory' => true])
                                @include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password'), 'mandatory' => true, 'extra_class' => 'password-changer'])
                                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
                                @include('commons.staticcontactswidget', ['obj' => $user])
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($user->isFriend() == false)
                                @if($currentuser->can('users.self', $currentgas))
                                    @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                                @else
                                    @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                                @endif

                                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])

                                @if($currentgas->getConfig('annual_fee_amount') != 0)
                                    @include('commons.staticmovementfield', [
                                        'obj' => $user->fee,
                                        'name' => 'fee_id',
                                        'label' => _i('Quota Associativa'),
                                        'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)
                                    ])
                                @endif

                                @if($currentgas->getConfig('deposit_amount') != 0)
                                    @include('commons.staticmovementfield', [
                                        'obj' => $user->deposit,
                                        'name' => 'deposit_id',
                                        'label' => _i('Deposito'),
                                        'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)
                                    ])
                                @endif

                                <?php $places = App\Delivery::orderBy('name', 'asc')->get() ?>
                                @if($places->isEmpty() == false)
                                    @include('commons.selectobjfield', [
                                        'obj' => $user,
                                        'name' => 'preferred_delivery_id',
                                        'objects' => $places,
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

            @if($user->isFriend() == false && App\Role::someone('movements.admin', $user->gas))
                <div role="tabpanel" class="tab-pane" id="accounting">
                    @include('movement.targetlist', ['target' => $user])
                </div>
            @endif

            @if($user->can('supplier.book'))
                <div role="tabpanel" class="tab-pane list-filter" id="bookings" data-list-target="#wrapper-booking-list">
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <form class="form-horizontal" data-toggle="validator" method="GET" action="{{ url('users/searchorders') }}">
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
                            </form>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            @include('commons.orderslist', ['orders' => $booked_orders])
                        </div>
                    </div>
                </div>
            @endif

            @if($user->can('users.subusers'))
                <div role="tabpanel" class="tab-pane" id="friends">
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
        </div>
    </div>
</div>

@stack('postponed')

@endsection

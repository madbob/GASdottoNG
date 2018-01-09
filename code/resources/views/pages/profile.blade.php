@extends($theme_layout)

@section('content')

<form class="form-horizontal inner-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.contactswidget', ['obj' => $user])

            @if($user->can('users.subusers'))
                <div class="form-group">
                    <label for="contacts" class="col-sm-{{ $labelsize }} control-label">{{ _i('Amici') }}</label>

                    <div class="col-sm-{{ $fieldsize }}">
                        @include('commons.manyrows', [
                            'contents' => $user->friends,
                            'columns' => [
                                [
                                    'label' => _i('ID'),
                                    'field' => 'id',
                                    'type' => 'hidden',
                                    'width' => 0,
                                    'extra' => [
                                        'prefix' => 'friend_'
                                    ]
                                ],
                                [
                                    'label' => _i('Username'),
                                    'field' => 'username',
                                    'type' => 'text',
                                    'width' => 10,
                                    'extra' => [
                                        'prefix' => 'friend_'
                                    ]
                                ],
                            ]
                        ])
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-6">
            @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])

            @if(Gate::check('users.admin', $currentgas))
                @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @else
                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @endif

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
        </div>
    </div>

    @if(App\Role::someone('movements.admin', $user->gas))
        @include('movement.targetlist', ['target' => $user])
    @endif

    <hr/>

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group">
                <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
            </div>
        </div>
    </div>
</form>

@stack('postponed')

@endsection

@extends($theme_layout)

@section('content')

<form class="form-horizontal inner-form" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.contactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @if(Gate::check('users.admin', $currentgas))
                @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
                @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])
            @else
                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])
            @endif

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
                @include('commons.movementfield', ['obj' => $user->fee, 'name' => 'fee_id', 'label' => 'Quota Associativa', 'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)])
                @include('commons.movementfield', ['obj' => $user->deposit, 'name' => 'deposit_id', 'label' => 'Deposito', 'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)])
            @else
                @include('commons.staticmovementfield', ['obj' => $user->fee, 'name' => 'fee_id', 'label' => 'Quota Associativa', 'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)])
                @include('commons.staticmovementfield', ['obj' => $user->deposit, 'name' => 'deposit_id', 'label' => 'Deposito', 'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)])
            @endif

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => 'Ultimo Accesso'])

            <?php $places = App\Delivery::orderBy('name', 'asc')->get() ?>
            @if($places->isEmpty() == false)
                @include('commons.selectobjfield', [
                    'obj' => $user,
                    'name' => 'preferred_delivery_id',
                    'objects' => $places,
                    'label' => 'Luogo di Consegna',
                    'extra_selection' => [
                        '0' => 'Nessuno'
                    ]
                ])
            @endif

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>

    @if(App\Role::someone('movements.admin', $user->gas))
        <hr/>
        <div class="page-header">
            <h3>Contabilità</h3>
        </div>
        <div class="row">
            <div class="col-md-12">
                <p class="lead">Saldo Corrente: <span>{{ $user->current_balance_amount }}</span> €</p>
            </div>
        </div>
        @include('movement.targetlist', ['target' => $user])
    @endif

    <hr/>

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
                <button type="submit" class="btn btn-success saving-button">Salva</button>
            </div>
        </div>
    </div>
</form>

@stack('postponed')

@endsection

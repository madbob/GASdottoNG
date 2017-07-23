<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}">
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
            @endif

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => 'Ultimo Accesso'])

            @include('commons.selectobjfield', [
                'obj' => $user,
                'name' => 'preferred_delivery_id',
                'objects' => App\Delivery::orderBy('name', 'asc')->get(),
                'label' => 'Luogo di Consegna',
                'extra_selection' => [
                    '0' => 'Nessuno'
                ]
            ])

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>

    @if(Gate::check('movements.admin', $currentgas))
        <hr/>
        <div class="page-header">
            <h3>Movimenti Contabili</h3>
        </div>
        @include('movement.targetlist', ['target' => $user])
    @endif

    @include('commons.formbuttons')
</form>

@stack('postponed')

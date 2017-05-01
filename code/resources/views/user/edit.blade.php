<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
        </div>
        <div class="col-md-6">
            @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])

            @if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
                @include('commons.movementfield', ['obj' => $user->fee, 'name' => 'fee_id', 'label' => 'Quota Associativa', 'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)])
                @include('commons.movementfield', ['obj' => $user->deposit, 'name' => 'deposit_id', 'label' => 'Deposito', 'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)])
            @endif

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => 'Ultimo Accesso'])
            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@stack('postponed')

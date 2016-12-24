<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => 'Data di Nascita'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => 'Codice Fiscale'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => 'Persone in Famiglia'])
        </div>
        <div class="col-md-6">
            @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])

            @if($currentgas->userCan('movements.view|movements.admin'))
                @include('commons.movementfield', ['obj' => $user->fee, 'name' => 'fee_id', 'label' => 'Quota Associativa', 'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)])
                @include('commons.movementfield', ['obj' => $user->deposit, 'name' => 'deposit_id', 'label' => 'Deposito', 'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)])
            @endif

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => 'Ultimo Accesso'])
        </div>
    </div>

    @if($currentgas->userCan('gas.permissions'))
        <hr/>

        <div class="row">
            <div class="col-md-6">
                @include('commons.permissionswidget', ['user' => $user])
            </div>
        </div>
    @endif

    @include('commons.formbuttons')
</form>

@stack('postponed')

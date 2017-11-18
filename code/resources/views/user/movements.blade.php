@if(Gate::check('users.admin', $currentgas) || Gate::check('users.movements', $currentgas))
    @if($currentgas->getConfig('annual_fee_amount') != 0)
        @if($editable && Gate::check('users.movements', $currentgas))
            @include('commons.movementfield', [
                'obj' => $user->fee,
                'name' => 'fee_id',
                'label' => 'Quota Associativa',
                'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)
            ])
        @else
            @include('commons.staticmovementfield', [
                'obj' => $user->fee,
                'name' => 'fee_id',
                'label' => 'Quota Associativa',
                'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)
            ])
        @endif
    @endif

    @if($currentgas->getConfig('deposit_amount') != 0)
        @if($editable && Gate::check('users.movements', $currentgas))
            @include('commons.movementfield', [
                'obj' => $user->deposit,
                'name' => 'deposit_id',
                'label' => 'Deposito',
                'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)
            ])
        @else
            @include('commons.staticmovementfield', [
                'obj' => $user->deposit,
                'name' => 'deposit_id',
                'label' => 'Deposito',
                'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)
            ])
        @endif
    @endif
@endif

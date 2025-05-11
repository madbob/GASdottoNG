@if(Gate::check('users.admin', $currentgas) || Gate::check('users.movements', $currentgas))
    @if($currentgas->getConfig('annual_fee_amount') != 0)
        @include('commons.staticmovementfield', [
            'obj' => $user->fee,
            'name' => 'fee_id',
            'label' => __('user.fee'),
            'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0),
            'help_popover' => __('user.help.fee'),
        ])
    @endif

    @if($currentgas->getConfig('deposit_amount') != 0)
        @php

        if ($editable && Gate::check('users.movements', $currentgas)) {
            $deposit_template = 'commons.movementfield';
        }
        else {
            $deposit_template = 'commons.staticmovementfield';
        }

        @endphp

        @include($deposit_template, [
            'obj' => $user->deposit,
            'name' => 'deposit_id',
            'label' => __('user.deposit'),
            'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0),
            'help_popover' => __('user.help.deposit'),
        ])
    @endif
@endif

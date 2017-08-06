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
                @if($currentgas->getConfig('annual_fee_amount') != 0)
                    @include('commons.movementfield', ['obj' => $user->fee, 'name' => 'fee_id', 'label' => 'Quota Associativa', 'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)])
                @endif
                @if($currentgas->getConfig('deposit_amount') != 0)
                    @include('commons.movementfield', ['obj' => $user->deposit, 'name' => 'deposit_id', 'label' => 'Deposito', 'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)])
                @endif
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

            @if(!empty($currentgas->rid_name))
                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">Configurazione RID/SEPA</label>

                    <div class="col-sm-{{ $fieldsize }}">
                        @include('commons.textfield', ['obj' => $user, 'name' => 'iban', 'label' => 'IBAN', 'squeeze' => true])
                        @include('commons.datefield', ['obj' => $user, 'name' => 'sepa_subscribe', 'label' => 'Sottoscrizione SEPA', 'squeeze' => true])
                    </div>
                </div>
            @endif

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>

    @if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
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

    @include('commons.formbuttons')
</form>

@stack('postponed')

<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.contactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @if(Gate::check('users.admin', $currentgas))
                @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => 'Foto', 'valuefrom' => 'picture_url'])
                @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
                @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])
            @else
                @include('commons.staticimagefield', ['obj' => $user, 'label' => 'Foto', 'valuefrom' => 'picture_url'])
                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => 'Numero Tessera'])
            @endif

            @if($currentgas->getConfig('annual_fee_amount') != 0)
                @include('commons.movementfield', [
                    'obj' => $user->fee,
                    'name' => 'fee_id',
                    'label' => 'Quota Associativa',
                    'default' => \App\Movement::generate('annual-fee', $user, $user->gas, 0)
                ])
            @endif

            @if($currentgas->getConfig('deposit_amount') != 0)
                @include('commons.movementfield', [
                    'obj' => $user->deposit,
                    'name' => 'deposit_id',
                    'label' => 'Deposito',
                    'default' => \App\Movement::generate('deposit-pay', $user, $user->gas, 0)
                ])
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

            <div class="form-group">
                <label class="col-sm-{{ $labelsize }} control-label">Stato</label>

                <div class="col-sm-{{ ceil($fieldsize / 2) }}">
                    <div class="btn-group" data-toggle="buttons">
                        <label class="btn btn-default {{ $user->deleted_at == null ? 'active' : '' }}">
                            <input type="radio" name="status" value="active" {{ $user->deleted_at == null ? 'checked' : '' }}> Attivo
                        </label>
                        <label class="btn btn-default {{ $user->suspended == true && $user->deleted_at != null ? 'active' : '' }}">
                            <input type="radio" name="status" value="suspended" {{ $user->suspended == true && $user->deleted_at != null ? 'checked' : '' }}> Sospeso
                        </label>
                        <label class="btn btn-default {{ $user->suspended == false && $user->deleted_at != null ? 'active' : '' }}">
                            <input type="radio" name="status" value="deleted" {{ $user->suspended == false && $user->deleted_at != null ? 'checked' : '' }}> Cessato
                        </label>
                    </div>
                </div>
                <div class="user-status-date col-sm-{{ floor($fieldsize / 2) }} {{ $user->deleted_at == null ? 'hidden' : '' }}">
                    @include('commons.datefield', ['obj' => $user, 'name' => 'deleted_at', 'label' => 'Data', 'squeeze' => true])
                </div>
            </div>

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>

    @if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
        @include('movement.targetlist', ['target' => $user])
    @endif

    @include('commons.formbuttons', ['obj' => $user, 'no_delete' => true])
</form>

@stack('postponed')

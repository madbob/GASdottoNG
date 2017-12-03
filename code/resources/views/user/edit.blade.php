<form class="form-horizontal main-form user-editor" method="PUT" action="{{ url('users/' . $user->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => 'Data di Nascita'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => 'Codice Fiscale'])
            @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => 'Persone in Famiglia'])
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

            @include('user.movements', ['editable' => true])

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

            @if(!empty($currentgas->rid['iban']))
                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">Configurazione SEPA</label>

                    <div class="col-sm-{{ $fieldsize }}">
                        @include('commons.textfield', ['obj' => $user, 'name' => 'rid->iban', 'label' => 'IBAN', 'squeeze' => true])

                        <div class="form-group">
                            <div class="col-sm-5">
                                @include('commons.textfield', ['obj' => $user, 'name' => 'rid->id', 'label' => 'Identificativo Mandato SEPA', 'squeeze' => true])
                            </div>
                            <div class="col-sm-7">
                                @include('commons.datefield', ['obj' => $user, 'name' => 'rid->date', 'label' => 'Data Mandato SEPA', 'squeeze' => true])
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user, 'editable' => true])
        </div>
    </div>

    @if(Gate::check('movements.admin', $currentgas) || Gate::check('movements.view', $currentgas))
        @include('movement.targetlist', ['target' => $user])
    @endif

    @include('commons.formbuttons', ['obj' => $user, 'no_delete' => true])
</form>

@stack('postponed')

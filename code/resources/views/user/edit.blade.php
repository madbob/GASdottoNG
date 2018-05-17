<form class="form-horizontal main-form user-editor" method="PUT" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.datefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
            @include('commons.textfield', ['obj' => $user, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
            @include('commons.textfield', ['obj' => $user, 'name' => 'family_members', 'label' => _i('Persone in Famiglia')])
            @include('commons.contactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @if($currentuser->can('users.admin', $currentgas))
                @include('commons.imagefield', ['obj' => $user, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                @include('commons.datefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.textfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @else
                @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
                @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
                @include('commons.staticstringfield', ['obj' => $user, 'name' => 'card_number', 'label' => _i('Numero Tessera')])
            @endif

            @include('user.movements', ['editable' => true])

            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])

            @if($currentgas->deliveries->isEmpty() == false)
                @include('commons.selectobjfield', [
                    'obj' => $user,
                    'name' => 'preferred_delivery_id',
                    'objects' => $currentgas->deliveries,
                    'label' => _i('Luogo di Consegna'),
                    'extra_selection' => [
                        '0' => _i('Nessuno')
                    ]
                ])
            @endif

            @include('commons.statusfield', ['target' => $user])

            @if($user->gas->hasFeature('rid'))
                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Configurazione SEPA') }}</label>

                    <div class="col-sm-{{ $fieldsize }}">
                        @include('commons.textfield', ['obj' => $user, 'name' => 'rid->iban', 'label' => _i('IBAN'), 'squeeze' => true])

                        <div class="form-group">
                            <div class="col-sm-5">
                                @include('commons.textfield', ['obj' => $user, 'name' => 'rid->id', 'label' => _i('Identificativo Mandato SEPA'), 'squeeze' => true])
                            </div>
                            <div class="col-sm-7">
                                @include('commons.datefield', ['obj' => $user, 'name' => 'rid->date', 'label' => _i('Data Mandato SEPA'), 'squeeze' => true])
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <hr/>
            @include('commons.permissionsviewer', ['object' => $user, 'editable' => true])
        </div>
    </div>

    @if($currentuser->can('movements.admin', $currentgas) || $currentuser->can('movements.view', $currentgas))
        <hr>
        @include('movement.targetlist', ['target' => $user])
    @endif

    @include('commons.formbuttons', ['obj' => $user, 'no_delete' => true])
</form>

@stack('postponed')

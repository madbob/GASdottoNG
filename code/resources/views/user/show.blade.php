<form class="form-horizontal main-form" method="PUT" action="{{ route('users.update', $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome'), 'mandatory' => true])
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome'), 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => _i('Data di Nascita')])
            @include('commons.staticcontactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @include('commons.staticimagefield', ['obj' => $user, 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => _i('Membro da')])
            @include('user.movements', ['editable' => $editable])
            <hr/>
            @include('commons.permissionsviewer', ['object' => $user, 'editable' => $editable])
        </div>
    </div>
</form>

@stack('postponed')

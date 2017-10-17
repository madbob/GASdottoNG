<form class="form-horizontal main-form" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => 'Nome', 'mandatory' => true])
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => 'Cognome', 'mandatory' => true])
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => 'Data di Nascita'])
            @include('commons.staticcontactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @include('commons.staticimagefield', ['obj' => $user, 'label' => 'Foto', 'valuefrom' => 'picture_url'])
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
            @include('user.movements')
            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>
</form>

@stack('postponed')

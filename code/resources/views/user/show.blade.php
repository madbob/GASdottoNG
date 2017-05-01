<form class="form-horizontal main-form" method="PUT" action="{{ url('users/' . $user->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'firstname', 'label' => 'Nome', 'mandatory' => true])
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'lastname', 'label' => 'Cognome', 'mandatory' => true])
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'phone', 'label' => 'Telefono'])
            @include('commons.staticstringfield', ['obj' => $user, 'name' => 'email', 'label' => 'E-Mail'])
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'birthday', 'label' => 'Data di Nascita'])
        </div>
        <div class="col-md-6">
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'member_since', 'label' => 'Membro da'])
            <hr/>
            @include('commons.permissionsviewer', ['object' => $user])
        </div>
    </div>
</form>

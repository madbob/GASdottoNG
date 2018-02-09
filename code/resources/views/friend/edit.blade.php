<form class="form-horizontal main-form friend-editor" method="PUT" action="{{ url('friends/' . $user->id) }}" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            @include('user.base-edit', ['user' => $user])
            @include('commons.contactswidget', ['obj' => $user])
        </div>
        <div class="col-md-6">
            @include('commons.staticdatefield', ['obj' => $user, 'name' => 'last_login', 'label' => _i('Ultimo Accesso')])
        </div>
    </div>

    @include('commons.formbuttons', ['obj' => $user])
</form>

@stack('postponed')

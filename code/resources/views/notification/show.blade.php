<div class="row">
    <div class="col-md-12">
        @include('commons.staticstringfield', ['obj' => $notification, 'name' => 'content', 'label' => _i('Contenuto')])
        @include('commons.staticobjectslistfield', ['obj' => $notification, 'name' => 'users', 'label' => _i('Destinatari')])
    </div>
</div>

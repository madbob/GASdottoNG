<div class="row">
    <div class="col-md-12">
        @include('commons.staticstringfield', ['obj' => $notification, 'name' => 'content', 'label' => _i('Contenuto')])
        @include('commons.staticdatefield', ['obj' => $notification, 'name' => 'start_date', 'label' => _i('Inizio')])
        @include('commons.staticdatefield', ['obj' => $notification, 'name' => 'end_date', 'label' => _i('Scadenza')])
    </div>
</div>

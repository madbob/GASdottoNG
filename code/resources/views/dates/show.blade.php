<div class="row">
    <div class="col-md-12">
        @include('commons.staticstringfield', ['obj' => $date, 'name' => 'content', 'label' => _i('Contenuto')])
        @include('commons.staticdatefield', ['obj' => $date, 'name' => 'date', 'label' => _i('Data')])
    </div>
</div>

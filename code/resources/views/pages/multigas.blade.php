@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @include('commons.addingbutton', [
            'template' => 'multigas.base-edit',
            'typename' => 'gas',
            'typename_readable' => _i('GAS'),
            'targeturl' => 'multigas'
        ])
    </div>
</div>

<div class="clearfix"></div>
<hr/>

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', [
            'identifier' => 'gas-list',
            'items' => $groups,
            'url' => url('multigas'),
            'legend' => (object)[
                'class' => 'Gas'
            ]
        ])
    </div>
</div>

@endsection

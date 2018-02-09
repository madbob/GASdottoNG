@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="page-header">
    <h3>{{ _i('Statistiche Generali') }}</h3>
</div>

<div class="row">
    <div class="col-md-6">
        <form id="stats-summary-form" class="form-horizontal">
            @include('commons.genericdaterange')

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-6">
        <div class="ct-chart-pie" id="stats-generic-expenses"></div>
    </div>
    <div class="col-md-6">
        <div class="ct-chart-bar" id="stats-generic-users"></div>
    </div>
</div>

<div class="page-header">
    <h3>{{ _i('Statistiche per Fornitore') }}</h3>
</div>

<div class="row">
    <div class="col-md-6">
        <form id="stats-supplier-form" class="form-horizontal">
            @include('commons.selectobjfield', [
                'obj' => null,
                'name' => 'supplier',
                'label' => _i('Fornitore'),
                'mandatory' => true,
                'objects' => $currentgas->suppliers
            ])

            @include('commons.genericdaterange')

            <div class="form-group">
                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-success">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-6">
        <div class="ct-chart-pie" id="stats-products-expenses"></div>
    </div>
    <div class="col-md-6">
        <div class="ct-chart-bar" id="stats-products-users"></div>
    </div>
</div>

@endsection

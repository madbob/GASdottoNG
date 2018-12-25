@extends('app')

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
                <div class="col-md-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-4">
        <h3>{{ _i('Valore Ordini') }}</h3>
        <div class="ct-chart-bar" id="stats-generic-expenses"></div>
    </div>
    <div class="col-md-4">
        <h3>{{ _i('Utenti Coinvolti') }}</h3>
        <div class="ct-chart-bar" id="stats-generic-users"></div>
    </div>
    <div class="col-md-4">
        <h3>{{ _i('Categorie') }}</h3>
        <div class="ct-chart-bar" id="stats-generic-categories"></div>
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
                <div class="col-md-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                    <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<hr/>

<div class="row">
    <div class="col-md-4">
        <h3>{{ _i('Valore Ordini') }}</h3>
        <div class="ct-chart-bar" id="stats-products-expenses"></div>
    </div>
    <div class="col-md-4">
        <h3>{{ _i('Utenti Coinvolti') }}</h3>
        <div class="ct-chart-bar" id="stats-products-users"></div>
    </div>
    <div class="col-md-4">
        <h3>{{ _i('Categorie') }}</h3>
        <div class="ct-chart-bar" id="stats-products-categories"></div>
    </div>
</div>

<div class="hidden" id="templates">
    <div class="alert alert-info">
        {{ _i('Non ci sono dati da visualizzare') }}
    </div>
</div>

@endsection

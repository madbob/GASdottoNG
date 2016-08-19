@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="page-header">
	<h3>Statistiche Generali</h3>
</div>

<div class="row">
        <form id="stats-summary-form" class="form-horizontal">
                <div class="col-md-6">
                        @include('commons.genericdaterange')

                        <div class="form-group">
                                <div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
                                        <button type="submit" class="btn btn-success">Ricerca</button>
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
	<h3>Statistiche per Fornitore</h3>
</div>

<div class="row">
        <form id="stats-supplier-form" class="form-horizontal">
                <div class="col-md-6">
                        @include('commons.selectobjfield', [
                                'obj' => null,
                                'name' => 'supplier',
                                'label' => 'Fornitore',
                                'mandatory' => true,
                                'objects' => App\Supplier::orderBy('name', 'asc')->get()
                        ])

                        @include('commons.genericdaterange')

                        <div class="form-group">
				<div class="col-sm-{{ $fieldsize }} col-md-offset-{{ $labelsize }}">
					<button type="submit" class="btn btn-success">Ricerca</button>
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

<input type="hidden" name="stats_target" value="{{ inlineId($target) }}">

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ _i('Statistiche Generali') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-lg-6">
                <form id="stats-summary-form" class="form-horizontal">
                    @include('commons.genericdaterange')

                    <div class="form-group">
                        <div class="col-12 col-sm-8 offset-sm-4">
                            <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-lg mb-2">
                <h4>{{ _i('Valore Ordini') }}</h4>
                <div class="ct-chart-bar" id="stats-generic-expenses"></div>
            </div>

			@if(is_a($target, \App\User::class) == false)
	            <div class="col-lg mb-2">
	                <h4>{{ _i('Utenti Coinvolti') }}</h4>
	                <div class="ct-chart-bar" id="stats-generic-users"></div>
	            </div>
			@endif

            <div class="col-lg mb-2">
                <h4>{{ _i('Categorie') }}</h4>
                <div class="ct-chart-bar" id="stats-generic-categories"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ _i('Statistiche per Fornitore') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-lg-6">
                <form id="stats-supplier-form" class="form-horizontal">
                    <x-larastrap::selectobj name="supplier" :label="_i('Fornitore')" :options="$currentgas->suppliers" />
                    @include('commons.genericdaterange')

                    <div class="form-group">
                        <div class="col-12 col-sm-8 offset-sm-4">
                            <button type="submit" class="btn btn-info">{{ _i('Ricerca') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-lg mb-2">
                <h4>{{ _i('Valore Ordini') }}</h4>
                <div class="ct-chart-bar" id="stats-products-expenses"></div>
            </div>

			@if(is_a($target, \App\User::class) == false)
	            <div class="col-lg mb-2">
	                <h4>{{ _i('Utenti Coinvolti') }}</h4>
	                <div class="ct-chart-bar" id="stats-products-users"></div>
	            </div>
			@endif

            <div class="col-lg mb-2">
                <h4>{{ _i('Categorie') }}</h4>
                <div class="ct-chart-bar" id="stats-products-categories"></div>
            </div>
        </div>
    </div>
</div>

<div class="hidden" id="templates">
    <div class="alert alert-info">
        {{ _i('Non ci sono dati da visualizzare') }}
    </div>
</div>

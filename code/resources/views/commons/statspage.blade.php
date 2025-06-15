@php

$shipped_count = App\Booking::where('status', 'shipped')->count();
if ($shipped_count == 0) {
    $default_type = 'all';
}
else {
    $default_type = 'shipped';
}

@endphp

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ __('texts.generic.stats.generic') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-lg-6">
                <form id="stats-summary-form" class="form-horizontal">
                    @include('commons.genericdaterange')
                    <x-larastrap::select name="type" tlabel="generic.type" :options="['all' => __('texts.generic.all'), 'shipped' => __('texts.orders.booking.statuses.shipped')]" :value="$default_type" />
                    <input type="hidden" name="target" value="{{ inlineId($target) }}">

                    <div class="form-group">
                        <div class="col-12 col-sm-8 offset-sm-4">
                            <button type="submit" class="btn btn-info" name="format" value="json">{{ __('texts.generic.search.all') }}</button>
                            <a href="{{ route('stats.show', 'summary') }}" class="btn btn-light form-download" name="format" value="csv">{{ __('texts.generic.exports.csv') }} <i class="bi-download"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-lg mb-2">
                <h4>{{ __('texts.generic.value') }}</h4>
                <div class="ct-chart-bar" id="stats-generic-expenses"></div>
            </div>

			@if(is_a($target, \App\User::class) == false)
	            <div class="col-lg mb-2">
	                <h4>{{ __('texts.user.all') }}</h4>
	                <div class="ct-chart-bar" id="stats-generic-users"></div>
	            </div>
			@endif

            <div class="col-lg mb-2">
                <h4>{{ __('texts.generic.categories') }}</h4>
                <div class="ct-chart-bar" id="stats-generic-categories"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ __('texts.generic.stats.supplier') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-lg-6">
                <form id="stats-supplier-form" class="form-horizontal">
                    <x-larastrap::select-model name="supplier" tlabel="orders.supplier" :options="$currentgas->suppliers" />
                    @include('commons.genericdaterange')
                    <x-larastrap::select name="type" tlabel="generic.type" :options="['all' => __('texts.generic.all'), 'shipped' => __('texts.orders.booking.statuses.shipped')]" :value="$default_type" />
                    <input type="hidden" name="target" value="{{ inlineId($target) }}">

                    <div class="form-group">
                        <div class="col-12 col-sm-8 offset-sm-4">
                            <button type="submit" class="btn btn-info">{{ __('texts.generic.search.all') }}</button>
                            <a href="{{ route('stats.show', 'supplier') }}" class="btn btn-light form-download" name="format" value="csv">{{ __('texts.generic.exports.csv') }} <i class="bi-download"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-lg mb-2">
                <h4>{{ __('texts.generic.value') }}</h4>
                <div class="ct-chart-bar" id="stats-products-expenses"></div>
            </div>

			@if(is_a($target, \App\User::class) == false)
	            <div class="col-lg mb-2">
	                <h4>{{ __('texts.user.all') }}</h4>
	                <div class="ct-chart-bar" id="stats-products-users"></div>
	            </div>
			@endif

            <div class="col-lg mb-2">
                <h4>{{ __('texts.generic.categories') }}</h4>
                <div class="ct-chart-bar" id="stats-products-categories"></div>
            </div>
        </div>
    </div>
</div>

<div class="hidden" id="templates">
    <x-larastrap::suggestion>
        {{ __('texts.generic.no_data') }}
    </x-larastrap::suggestion>
</div>

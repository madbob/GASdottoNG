<?php $currencies = App\Currency::enabled() ?>
@if($currencies->count() > 1)
    <x-larastrap::text name="amount" :label="_i('Valore')" classes="number, trim-2-ddigits" required />
    <x-larastrap::selectobj name="currency_id" :label="_i('Valuta')" :options="$currencies" />
@else
    <x-larastrap::price name="amount" :label="_i('Valore')" required />
@endif

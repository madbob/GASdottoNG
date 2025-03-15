<?php $currencies = App\Currency::enabled() ?>
@if($currencies->count() > 1)
    <x-larastrap::price name="amount" :label="_i('Valore')" required />
    <x-larastrap::select-model name="currency_id" :label="_i('Valuta')" :options="$currencies" />
@else
    <x-larastrap::price name="amount" :label="_i('Valore')" required :attributes="['data-allow-negative' => $allow_negative ? '1' : '0']" />
@endif
